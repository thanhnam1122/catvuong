<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Quản Lý Hóa Đơn Xuất Ra (Bán hàng / Cung cấp dịch vụ)
     */
    public function outInvoices(Request $request)
    {
        return $this->renderFlow($request, 'out');
    }

    /**
     * Quản Lý Hóa Đơn Đầu Vào (Mua hàng / Chi phí)
     */
    public function inInvoices(Request $request)
    {
        return $this->renderFlow($request, 'in');
    }

    /**
     * Xử lý hiển thị danh sách hóa đơn theo luồng
     */
    private function renderFlow(Request $request, string $flow)
    {
        $companyId = $request->get('company_id');
        $status = $request->get('status'); // unpaid, paid, all
        $search = $request->get('search');

        $companies = Company::orderBy('name')->get();
        $partners = Partner::orderBy('name')->get();

        $query = Invoice::with(['company', 'partner', 'debt'])
            ->where('type', $flow);

        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($status && in_array($status, ['unpaid', 'paid', 'cancelled'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('partner', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('issue_date', 'desc')->paginate(20)->withQueryString();

        // Thống kê riêng cho luồng này theo công ty đang lọc
        $statsQuery = Invoice::where('type', $flow);
        if ($companyId && $companyId !== 'all') {
            $statsQuery->where('company_id', $companyId);
        }

        $totalAmount = (clone $statsQuery)->sum('total_amount');
        $totalPaid = (clone $statsQuery)->sum('paid_amount');
        $totalUnpaid = max(0, $totalAmount - $totalPaid);
        $totalTax = (clone $statsQuery)->sum('tax_amount');
        $unpaidCount = (clone $statsQuery)->where('status', 'unpaid')->count();

        return view('invoice.flow', compact(
            'flow',
            'companies',
            'partners',
            'invoices',
            'companyId',
            'status',
            'search',
            'totalAmount',
            'totalPaid',
            'totalUnpaid',
            'totalTax',
            'unpaidCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'nullable',
            'new_partner_name' => 'nullable|string|max:255',
            'type' => 'required|in:out,in',
            'invoice_number' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'paid_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:unpaid,paid,cancelled',
            'notes' => 'nullable|string',
            'sync_debt' => 'nullable|boolean',
        ]);

        // Tạo đối tác mới nhanh nếu có
        if (empty($validated['partner_id']) && !empty($validated['new_partner_name'])) {
            $partner = Partner::create([
                'company_id' => $validated['company_id'],
                'name' => trim($validated['new_partner_name']),
                'type' => $validated['type'] === 'out' ? 'customer' : 'supplier',
                'phone' => $request->input('new_partner_phone'),
            ]);
            $partnerId = $partner->id;
        } else {
            $partnerId = $validated['partner_id'];
        }

        if (!$partnerId) {
            return back()->with('error', 'Vui lòng chọn hoặc nhập tên đối tác/khách hàng.')->withInput();
        }

        // Tự sinh số hóa đơn nếu bỏ trống
        $invoiceNumber = $validated['invoice_number'];
        if (empty($invoiceNumber)) {
            $prefix = $validated['type'] === 'out' ? 'HD-XUAT' : 'HD-VAO';
            $invoiceNumber = $prefix . '-' . date('ymd') . '-' . rand(100, 999);
        }

        $subtotal = (float)$validated['subtotal'];
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        $paidAmount = (float)($validated['paid_amount'] ?? 0);
        $status = $validated['status'];
        if ($status === 'paid' && $paidAmount < $totalAmount) {
            $paidAmount = $totalAmount;
        } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
        }

        $debtId = null;

        DB::transaction(function () use (
            $validated,
            $invoiceNumber,
            $partnerId,
            $subtotal,
            $taxRate,
            $taxAmount,
            $totalAmount,
            $paidAmount,
            $status,
            &$debtId
        ) {
            // Tùy chọn đồng bộ công nợ
            if (!empty($validated['sync_debt'])) {
                $debtType = $validated['type'] === 'out' ? 'receivable' : 'payable';
                $debtCode = 'CN-' . ($debtType === 'receivable' ? 'THU' : 'TRA') . '-' . date('ymd') . '-' . rand(100, 999);

                $debt = Debt::create([
                    'code' => $debtCode,
                    'company_id' => $validated['company_id'],
                    'partner_id' => $partnerId,
                    'type' => $debtType,
                    'title' => "Hóa đơn {$invoiceNumber}: " . $validated['title'],
                    'amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'status' => $status === 'paid' ? 'completed' : ($paidAmount > 0 ? 'partial' : 'pending'),
                    'notes' => "Tự động sinh từ hóa đơn {$invoiceNumber}",
                ]);
                $debtId = $debt->id;
            }

            Invoice::create([
                'invoice_number' => $invoiceNumber,
                'company_id' => $validated['company_id'],
                'partner_id' => $partnerId,
                'debt_id' => $debtId,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $flowName = $validated['type'] === 'out' ? 'Hóa đơn xuất ra' : 'Hóa đơn đầu vào';
        return redirect()->back()->with('success', "Đã tạo {$flowName} [{$invoiceNumber}] thành công!");
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'required|exists:partners,id',
            'type' => 'required|in:out,in',
            'invoice_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'paid_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:unpaid,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $subtotal = (float)$validated['subtotal'];
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        $paidAmount = (float)($validated['paid_amount'] ?? $invoice->paid_amount);
        $status = $validated['status'];
        if ($status === 'paid') {
            $paidAmount = $totalAmount;
        } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
        }

        $invoice->update([
            'company_id' => $validated['company_id'],
            'partner_id' => $validated['partner_id'],
            'type' => $validated['type'],
            'invoice_number' => $validated['invoice_number'],
            'title' => $validated['title'],
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($invoice->debt) {
            $invoice->debt->update([
                'amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'company_id' => $validated['company_id'],
                'partner_id' => $validated['partner_id'],
            ]);
            $invoice->debt->updateStatusAutomatically();
        }

        return redirect()->back()->with('success', "Cập nhật hóa đơn {$invoice->invoice_number} thành công!");
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $number = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->back()->with('success', "Đã xóa hóa đơn {$number}!");
    }

    public function pay(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $paymentAmount = (float)$validated['amount'];
        $newPaidAmount = (float)$invoice->paid_amount + $paymentAmount;

        if ($newPaidAmount >= (float)$invoice->total_amount) {
            $invoice->paid_amount = (float)$invoice->total_amount;
            $invoice->status = 'paid';
        } else {
            $invoice->paid_amount = $newPaidAmount;
        }
        $invoice->save();

        if ($invoice->debt) {
            $invoice->debt->paid_amount = $invoice->paid_amount;
            $invoice->debt->updateStatusAutomatically();
        }

        return redirect()->back()->with('success', "Đã ghi nhận thanh toán " . number_format($paymentAmount) . " đ cho hóa đơn {$invoice->invoice_number}!");
    }

    public function toggleStatus($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status === 'paid') {
            $invoice->status = 'unpaid';
            $invoice->paid_amount = 0;
        } else {
            $invoice->status = 'paid';
            $invoice->paid_amount = $invoice->total_amount;
        }
        $invoice->save();

        if ($invoice->debt) {
            $invoice->debt->paid_amount = $invoice->paid_amount;
            $invoice->debt->updateStatusAutomatically();
        }

        return redirect()->back()->with('success', "Đã cập nhật trạng thái hóa đơn {$invoice->invoice_number} sang: " . ($invoice->status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'));
    }

    public function print($id)
    {
        $invoice = Invoice::with(['company', 'partner'])->findOrFail($id);
        return view('invoice.print', compact('invoice'));
    }
}
