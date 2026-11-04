<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use App\Models\InvoiceAttachment;
use App\Models\Debt;
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
            $partners = Partner::orderBy('name')->get();
        }

        $partnerId = $request->get('partner_id');
        $status = $request->get('status'); // unpaid, paid, all
        $search = $request->get('search');
        $concern = $request->get('concern'); // all, 1 (quan tâm), 0 (không quan tâm)

        $query = Invoice::with(['company', 'partner', 'debt', 'attachments'])
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

        // Thống kê riêng cho luồng này theo bộ lọc
        $statsQuery = Invoice::where('type', $flow);
        if ($companyId && $companyId !== 'all') {
            $statsQuery->where('company_id', $companyId);
        }
        if ($partnerId && $partnerId !== 'all') {
            $statsQuery->where('partner_id', $partnerId);
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
            'paid_amount' => 'nullable|numeric|min:0',
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
            'paid_amount' => 'nullable|numeric|min:0',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,partial,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $subtotal = (float)$validated['subtotal'];
        $taxRate = (float)$validated['tax_rate'];
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;

        $paidAmount = isset($validated['paid_amount']) ? (float)$validated['paid_amount'] : (float)$invoice->paid_amount;
        $reqStatus = $validated['status'] ?? $invoice->status;

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
            'notes' => 'nullable|string',
        ]);

        $paymentAmount = (float)$validated['amount'];
        $newPaidAmount = (float)$invoice->paid_amount + $paymentAmount;
        $totalAmount = (float)$invoice->total_amount;

        if ($newPaidAmount >= $totalAmount) {
            $invoice->paid_amount = $totalAmount;
            $invoice->status = 'paid';
        } elseif ($newPaidAmount > 0) {
            $invoice->paid_amount = $newPaidAmount;
            $invoice->status = 'partial';
        } else {
            $invoice->paid_amount = 0;
            $invoice->status = 'unpaid';
        }
        $invoice->save();

        if ($invoice->debt) {
            $invoice->debt->paid_amount = $invoice->paid_amount;
            $invoice->debt->updateStatusAutomatically();
        }

        $remaining = max(0, $totalAmount - $invoice->paid_amount);
        $statusText = $invoice->status === 'paid' ? 'Đã thanh toán đủ' : 'Thanh toán 1 phần (Còn thiếu ' . number_format($remaining, 0, ',', '.') . ' đ)';

        return redirect()->back()->with('success', "Đã ghi nhận thanh toán " . number_format($paymentAmount, 0, ',', '.') . " đ cho hóa đơn {$invoice->invoice_number}! Trạng thái: {$statusText}.");
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
