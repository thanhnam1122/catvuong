<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\Debt;
use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $currentUser = Auth::user();
        $isCompanyUser = $currentUser && !$currentUser->isAdmin();

        if ($isCompanyUser) {
            $companyId = $currentUser->company_id;
            $companies = Company::where('id', $companyId)->get();
            $partners = Partner::where('company_id', $companyId)->orderBy('name')->get();
        } else {
            $companyId = $request->get('company_id');
            $companies = Company::orderBy('name')->get();
            if ($companyId && $companyId !== 'all') {
                $partners = Partner::where('company_id', $companyId)->orderBy('name')->get();
            } else {
                $partners = Partner::orderBy('name')->get();
            }
        }

        $partnerId = $request->get('partner_id');
        $status = $request->get('status'); // unpaid, paid, all
        $search = $request->get('search');
        $concern = $request->get('concern'); // all, 1 (quan tâm), 0 (không quan tâm)
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $sortBy = $request->get('sort_by');
        $order = strtolower($request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Invoice::with(['company', 'partner', 'debt', 'attachments', 'payments'])
            ->withCount('attachments')
            ->where('type', $flow);

        if ($companyId && $companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($partnerId && $partnerId !== 'all') {
            $query->where('partner_id', $partnerId);
        }

        if ($status && in_array($status, ['unpaid', 'partial', 'paid', 'cancelled'])) {
            $query->where('status', $status);
        }

        if ($concern !== null && $concern !== '' && $concern !== 'all') {
            $query->where('is_concerned', (bool)$concern);
        }

        if ($fromDate) {
            $query->whereDate('issue_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('issue_date', '<=', $toDate);
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

        if ($sortBy === 'number') {
            if (config('database.default') === 'pgsql') {
                $query->orderByRaw("CASE WHEN invoice_number ~ '^[0-9]+$' THEN invoice_number::bigint ELSE NULL END {$order} NULLS LAST, invoice_number {$order}");
            } else {
                $query->orderBy('invoice_number', $order);
            }
        } else {
            $query->orderBy('issue_date', 'desc')->orderBy('id', 'desc');
        }

        $invoices = $query->paginate(20)->withQueryString();

        // Thống kê riêng cho luồng này theo bộ lọc
        $statsQuery = Invoice::where('type', $flow);
        if ($companyId && $companyId !== 'all') {
            $statsQuery->where('company_id', $companyId);
        }
        if ($partnerId && $partnerId !== 'all') {
            $statsQuery->where('partner_id', $partnerId);
        }
        if ($fromDate) {
            $statsQuery->whereDate('issue_date', '>=', $fromDate);
        }
        if ($toDate) {
            $statsQuery->whereDate('issue_date', '<=', $toDate);
        }

        $concernedCount = (clone $statsQuery)->where('is_concerned', true)->count();
        $unconcernedCount = (clone $statsQuery)->where('is_concerned', false)->count();

        if ($concern !== null && $concern !== '' && $concern !== 'all') {
            $statsQuery->where('is_concerned', (bool)$concern);
        }

        $totalAmount = (clone $statsQuery)->sum('total_amount');
        $totalPaid = (clone $statsQuery)->sum('paid_amount');
        $totalUnpaid = max(0, $totalAmount - $totalPaid);
        $totalTax = (clone $statsQuery)->sum('tax_amount');
        $unpaidCount = (clone $statsQuery)->whereIn('status', ['unpaid', 'partial'])->count();

        return view('invoice.flow', compact(
            'flow',
            'companies',
            'partners',
            'invoices',
            'companyId',
            'partnerId',
            'status',
            'concern',
            'concernedCount',
            'unconcernedCount',
            'search',
            'fromDate',
            'toDate',
            'sortBy',
            'order',
            'totalAmount',
            'totalPaid',
            'totalUnpaid',
            'totalTax',
            'unpaidCount'
        ));
    }

    public function store(Request $request)
    {
        if ($request->has('subtotal')) {
            $request->merge(['subtotal' => str_replace(',', '', (string)$request->subtotal)]);
        }
        if ($request->has('tax_amount')) {
            $request->merge(['tax_amount' => str_replace(',', '', (string)$request->tax_amount)]);
        }
        if ($request->has('paid_amount')) {
            $request->merge(['paid_amount' => str_replace(',', '', (string)$request->paid_amount)]);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'nullable',
            'new_partner_name' => 'nullable|string|max:255',
            'type' => 'required|in:out,in',
            'is_concerned' => 'nullable',
            'invoice_number' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,partial,paid,cancelled',
            'notes' => 'nullable|string',
            'sync_debt' => 'nullable|boolean',
        ]);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $validated['company_id'] = $currentUser->company_id;
        }

        // Tạo đối tác mới nhanh nếu có
        $rawPartnerId = $validated['partner_id'] ?? null;
        if (($rawPartnerId === 'NEW' || empty($rawPartnerId)) && !empty($validated['new_partner_name'])) {
            $partner = Partner::create([
                'company_id' => $validated['company_id'],
                'name' => trim($validated['new_partner_name']),
                'type' => $validated['type'] === 'out' ? 'customer' : 'supplier',
                'phone' => $request->input('new_partner_phone'),
            ]);
            $partnerId = $partner->id;
        } elseif (is_numeric($rawPartnerId)) {
            $partnerId = (int)$rawPartnerId;
        } else {
            $partnerId = null;
        }

        if (!$partnerId) {
            return back()->with('error', 'Vui lòng chọn hoặc nhập tên đối tác/khách hàng hợp lệ.')->withInput();
        }

        // Tự sinh số hóa đơn nếu bỏ trống hoặc kiểm tra trùng số hóa đơn
        $invoiceNumber = trim((string)($validated['invoice_number'] ?? ''));
        if (empty($invoiceNumber)) {
            $prefix = $validated['type'] === 'out' ? 'HD-XUAT' : 'HD-VAO';
            $invoiceNumber = $prefix . '-' . date('ymd') . '-' . rand(100, 999);
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber = $prefix . '-' . date('ymd') . '-' . rand(1000, 9999);
            }
        } else {
            // Kiểm tra trùng lặp trong cùng công ty / đối tác
            $dupQuery = Invoice::where('company_id', $validated['company_id'])
                ->where('type', $validated['type'])
                ->where('invoice_number', $invoiceNumber);

            if ($validated['type'] === 'in') {
                $dupQuery->where('partner_id', $partnerId);
            }

            if ($dupQuery->exists()) {
                $msg = $validated['type'] === 'out'
                    ? "Số hóa đơn [{$invoiceNumber}] đã tồn tại trong công ty này. Vui lòng nhập số khác."
                    : "Đối tác này đã có hóa đơn số [{$invoiceNumber}]. Vui lòng nhập số khác.";
                return back()->with('error', $msg)->withInput();
            }
        }

        $subtotal = (float)$validated['subtotal'];
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = isset($validated['tax_amount']) && $validated['tax_amount'] !== '' 
            ? (float)$validated['tax_amount'] 
            : round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        $paidAmount = (float)($validated['paid_amount'] ?? 0);
        $reqStatus = $validated['status'] ?? 'unpaid';

        if ($reqStatus === 'cancelled') {
            $status = 'cancelled';
        } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
            $paidAmount = $totalAmount;
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
            $paidAmount = 0;
        }

        $debtId = null;

        $isConcerned = $request->has('is_concerned') ? (bool)$request->input('is_concerned') : true;

        DB::transaction(function () use (
            $validated,
            $invoiceNumber,
            $partnerId,
            $isConcerned,
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
                while (Debt::where('code', $debtCode)->exists()) {
                    $debtCode = 'CN-' . ($debtType === 'receivable' ? 'THU' : 'TRA') . '-' . date('ymd') . '-' . rand(1000, 9999);
                }

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
                'is_concerned' => $isConcerned,
                'title' => $validated['title'],
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'paid_date' => $paidAmount > 0 ? ($validated['paid_date'] ?? date('Y-m-d')) : null,
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

        if ($request->has('subtotal')) {
            $request->merge(['subtotal' => str_replace(',', '', (string)$request->subtotal)]);
        }
        if ($request->has('tax_amount')) {
            $request->merge(['tax_amount' => str_replace(',', '', (string)$request->tax_amount)]);
        }
        if ($request->has('paid_amount')) {
            $request->merge(['paid_amount' => str_replace(',', '', (string)$request->paid_amount)]);
        }

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'partner_id' => 'required|exists:partners,id',
            'type' => 'required|in:out,in',
            'is_concerned' => 'nullable',
            'invoice_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,partial,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $dupQuery = Invoice::where('company_id', $validated['company_id'])
            ->where('type', $validated['type'])
            ->where('invoice_number', $validated['invoice_number'])
            ->where('id', '!=', $id);

        if ($validated['type'] === 'in') {
            $dupQuery->where('partner_id', $validated['partner_id']);
        }

        if ($dupQuery->exists()) {
            $msg = $validated['type'] === 'out'
                ? "Số hóa đơn [{$validated['invoice_number']}] đã tồn tại trong công ty này."
                : "Đối tác này đã có hóa đơn số [{$validated['invoice_number']}].";
            return back()->with('error', $msg)->withInput();
        }

        $subtotal = (float)$validated['subtotal'];
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = isset($validated['tax_amount']) && $validated['tax_amount'] !== '' 
            ? (float)$validated['tax_amount'] 
            : round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        $paidAmount = isset($validated['paid_amount']) ? (float)$validated['paid_amount'] : (float)$invoice->paid_amount;
        $reqStatus = $validated['status'] ?? $invoice->status;
        $paidDate = $request->input('paid_date');

        if ($reqStatus === 'cancelled') {
            $status = 'cancelled';
        } elseif ($reqStatus === 'paid') {
            $status = 'paid';
            if ($paidAmount <= 0) {
                $paidAmount = $totalAmount;
            }
        } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
            $paidAmount = $totalAmount;
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
            $paidAmount = 0;
        }

        // Xử lý Ngày thanh toán
        if (!empty($paidDate)) {
            // Người dùng có nhập ngày thanh toán
            if ($status === 'unpaid' && $paidAmount <= 0) {
                $status = 'paid';
                $paidAmount = $totalAmount;
            }
        } elseif ($paidAmount > 0) {
            // Có thanh toán nhưng để trống ngày -> giữ ngày cũ hoặc hôm nay
            $paidDate = $invoice->paid_date ?? date('Y-m-d');
        } else {
            $paidDate = null;
        }

        $invoice->update([
            'company_id' => $validated['company_id'],
            'partner_id' => $validated['partner_id'],
            'type' => $validated['type'],
            'is_concerned' => $request->has('is_concerned') ? (bool)$request->input('is_concerned') : $invoice->is_concerned,
            'invoice_number' => $validated['invoice_number'],
            'title' => $validated['title'],
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'paid_date' => $paidDate,
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

        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace(',', '', (string)$request->amount)]);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'reference_code' => 'nullable|string',
        ]);

        $paymentAmount = (float)$validated['amount'];

        // Tạo bản ghi đợt thanh toán vào lịch sử thanh toán
        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'amount' => $paymentAmount,
            'payment_date' => $validated['payment_date'] ?? date('Y-m-d'),
            'note' => $validated['notes'] ?? null,
            'payment_method' => $request->get('payment_method', 'transfer'),
            'reference_code' => $request->get('reference_code'),
        ]);

        // Tự động đồng bộ lại paid_amount, paid_date và status của hóa đơn
        $invoice->syncPaymentsStatus();

        $totalAmount = (float)$invoice->total_amount;
        $remaining = max(0, $totalAmount - $invoice->paid_amount);
        $surplus = max(0, $invoice->paid_amount - $totalAmount);

        if ($surplus > 0) {
            $statusText = 'Đã thanh toán đủ (Thanh toán thừa +' . number_format($surplus, 0, ',', '.') . ' đ bù HĐ khác)';
        } elseif ($invoice->status === 'paid') {
            $statusText = 'Đã thanh toán đủ';
        } else {
            $statusText = 'Thanh toán 1 phần (Còn thiếu ' . number_format($remaining, 0, ',', '.') . ' đ)';
        }

        return redirect()->back()->with('success', "Đã ghi nhận đợt thanh toán " . number_format($paymentAmount, 0, ',', '.') . " đ cho hóa đơn {$invoice->invoice_number}! Trạng thái: {$statusText}.");
    }

    /**
     * Lấy danh sách lịch sử thanh toán của hóa đơn (AJAX/JSON)
     */
    public function getPayments($id)
    {
        $invoice = Invoice::with(['payments' => function($q) {
            $q->orderBy('payment_date', 'asc')->orderBy('id', 'asc');
        }, 'partner'])->findOrFail($id);

        return response()->json([
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'partner_name' => $invoice->partner ? $invoice->partner->name : '',
            'total_amount' => (float)$invoice->total_amount,
            'paid_amount' => (float)$invoice->paid_amount,
            'remaining_amount' => (float)$invoice->remaining_amount,
            'surplus_amount' => (float)$invoice->surplus_amount,
            'status' => $invoice->status,
            'payments' => $invoice->payments,
        ]);
    }

    /**
     * Xóa 1 đợt thanh toán khỏi lịch sử
     */
    public function deletePayment($id)
    {
        $payment = InvoicePayment::findOrFail($id);
        $invoice = $payment->invoice;
        $amount = $payment->amount;
        $payment->delete();

        // Đồng bộ lại hóa đơn
        $invoice->syncPaymentsStatus();

        return redirect()->back()->with('success', "Đã xóa đợt thanh toán " . number_format($amount, 0, ',', '.') . " đ của hóa đơn {$invoice->invoice_number}.");
    }

    public function toggleStatus($id)
    {
        $invoice = Invoice::findOrFail($id);
        if ($invoice->status === 'paid') {
            // Chuyển về Chưa thanh toán: Xóa các đợt thanh toán
            $invoice->payments()->delete();
            $invoice->status = 'unpaid';
            $invoice->paid_amount = 0;
            $invoice->paid_date = null;
            $invoice->save();
        } else {
            // Chuyển sang Đã thanh toán: Nếu chưa có đợt thanh toán nào thì tạo 1 đợt thanh toán đủ
            if ($invoice->payments()->count() === 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total_amount,
                    'payment_date' => date('Y-m-d'),
                    'note' => 'Thanh toán đủ',
                    'payment_method' => 'transfer',
                ]);
            }
            $invoice->syncPaymentsStatus();
        }

        if ($invoice->debt) {
            $invoice->debt->paid_amount = $invoice->paid_amount;
            $invoice->debt->updateStatusAutomatically();
        }

        return redirect()->back()->with('success', "Đã cập nhật trạng thái hóa đơn {$invoice->invoice_number} sang: " . ($invoice->status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'));
    }

    /**
     * Chuyển đổi nhanh phân loại Quan tâm / Không quan tâm
     */
    public function toggleConcern(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($invoice->company_id != $currentUser->company_id) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Bạn không có quyền thao tác trên hóa đơn này.'], 403);
                }
                abort(403, 'Bạn không có quyền thao tác.');
            }
        }

        $invoice->is_concerned = !$invoice->is_concerned;
        $invoice->save();

        $statusLabel = $invoice->is_concerned ? 'Quan tâm' : 'Không quan tâm';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_concerned' => $invoice->is_concerned,
                'label' => $statusLabel,
                'message' => "Đã chuyển phân loại hóa đơn [{$invoice->invoice_number}] thành: {$statusLabel}",
            ]);
        }

        return redirect()->back()->with('success', "Đã chuyển phân loại hóa đơn [{$invoice->invoice_number}] thành: {$statusLabel}");
    }

    public function print($id)
    {
        $invoice = Invoice::with(['company', 'partner'])->findOrFail($id);
        return view('invoice.print', compact('invoice'));
    }

    /**
     * Lấy danh sách file đính kèm của hóa đơn (AJAX/API)
     */
    public function getAttachments($id)
    {
        $invoice = Invoice::with('attachments')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($invoice->company_id != $currentUser->company_id) {
                return response()->json(['error' => 'Bạn không có quyền truy cập hóa đơn này.'], 403);
            }
        }

        $attachments = $invoice->attachments->map(function ($att) {
            return [
                'id' => $att->id,
                'category' => $att->category,
                'category_label' => $att->category_label,
                'category_badge' => $att->category_short_badge,
                'file_name' => $att->file_name,
                'file_size' => $att->formatted_size,
                'mime_type' => $att->mime_type,
                'is_image' => $att->is_image,
                'is_pdf' => $att->is_pdf,
                'notes' => $att->notes,
                'uploaded_by' => $att->uploaded_by,
                'created_at' => $att->created_at ? $att->created_at->format('d/m/Y H:i') : '',
                'view_url' => route('invoice.attachments.view', $att->id),
                'download_url' => route('invoice.attachments.download', $att->id),
            ];
        });

        return response()->json([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'title' => $invoice->title,
                'total_amount' => number_format((float)$invoice->total_amount, 0, ',', '.') . ' đ',
                'partner_name' => $invoice->partner ? $invoice->partner->name : '---',
            ],
            'attachments' => $attachments,
            'count' => $attachments->count(),
        ]);
    }

    /**
     * Tải file đính kèm lên hóa đơn (HĐ, BG, HS liên quan, Hóa đơn)
     */
    public function uploadAttachments(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($invoice->company_id != $currentUser->company_id) {
                return response()->json(['error' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
            }
        }

        $validated = $request->validate([
            'category' => 'required|in:invoice,contract,quote,doc,unc',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:20480', // tối đa 20MB / file
            'notes' => 'nullable|string|max:255',
        ], [
            'category.required' => 'Vui lòng chọn phân loại tài liệu (HĐ, BG, HS liên quan, Hóa đơn, Ủy nhiệm chi).',
            'files.required' => 'Vui lòng chọn ít nhất 1 file đính kèm.',
            'files.*.max' => 'File tải lên không được vượt quá 20MB.',
        ]);

        $uploadedCount = 0;
        $category = $validated['category'];
        $notes = $validated['notes'] ?? null;
        $userName = $currentUser ? $currentUser->name : 'Người dùng';

        foreach ($request->file('files') as $file) {
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();

            // Mã hóa base64 để lưu trực tiếp trong PostgreSQL (đảm bảo vĩnh viễn trên cả Vercel và máy cục bộ)
            $fileData = base64_encode(file_get_contents($file->getRealPath()));

            InvoiceAttachment::create([
                'invoice_id' => $invoice->id,
                'category' => $category,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'file_data' => $fileData,
                'notes' => $notes,
                'uploaded_by' => $userName,
            ]);

            $uploadedCount++;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã đính kèm thành công {$uploadedCount} file vào hóa đơn {$invoice->invoice_number}!",
                'count' => $invoice->attachments()->count(),
            ]);
        }

        return redirect()->back()->with('success', "Đã đính kèm thành công {$uploadedCount} file vào hóa đơn {$invoice->invoice_number}!");
    }

    /**
     * Xem trực tiếp file đính kèm (Ảnh / PDF)
     */
    public function viewAttachment($id)
    {
        $attachment = InvoiceAttachment::with('invoice')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->invoice && $attachment->invoice->company_id != $currentUser->company_id) {
                abort(403, 'Bạn không có quyền xem file này.');
            }
        }

        if (empty($attachment->file_data)) {
            abort(404, 'Dữ liệu file không tồn tại.');
        }

        $binary = base64_decode($attachment->file_data);
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        $safeFilename = str_replace('"', '', $attachment->file_name);

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . rawurlencode($safeFilename) . '"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Tải file đính kèm về máy
     */
    public function downloadAttachment($id)
    {
        $attachment = InvoiceAttachment::with('invoice')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->invoice && $attachment->invoice->company_id != $currentUser->company_id) {
                abort(403, 'Bạn không có quyền tải file này.');
            }
        }

        if (empty($attachment->file_data)) {
            abort(404, 'Dữ liệu file không tồn tại.');
        }

        $binary = base64_decode($attachment->file_data);
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        $safeFilename = str_replace('"', '', $attachment->file_name);

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($safeFilename) . '"',
        ]);
    }

    /**
     * Xóa file đính kèm
     */
    public function deleteAttachment($id)
    {
        $attachment = InvoiceAttachment::with('invoice')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->invoice && $attachment->invoice->company_id != $currentUser->company_id) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['error' => 'Bạn không có quyền xóa file này.'], 403);
                }
                abort(403, 'Bạn không có quyền xóa file này.');
            }
        }

        $fileName = $attachment->file_name;
        $invoiceId = $attachment->invoice_id;
        $attachment->delete();

        if (request()->ajax() || request()->wantsJson()) {
            $remainingCount = InvoiceAttachment::where('invoice_id', $invoiceId)->count();
            return response()->json([
                'success' => true,
                'message' => "Đã xóa file [{$fileName}] thành công!",
                'count' => $remainingCount,
            ]);
        }

        return redirect()->back()->with('success', "Đã xóa file [{$fileName}] thành công!");
    }
}
