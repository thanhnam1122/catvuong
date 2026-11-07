<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Partner;
use App\Models\PartnerAttachment;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Quản lý Doanh nghiệp (Nhà Cung Cấp / Đối Tác)
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // all, supplier, customer, both
        $search = $request->get('search');
        $companyId = $request->get('company_id', 'all');

        $user = Auth::user();
        $isAdmin = $user ? $user->isAdmin() : true;

        $query = Partner::withCount(['invoices', 'debts', 'attachments'])->with(['company']);

        // Phân quyền dữ liệu theo vai trò tài khoản:
        // Tài khoản CTY: chỉ hiển thị doanh nghiệp do chính CTY đó nhập vào
        if (!$isAdmin && $user && $user->company_id) {
            $companyId = $user->company_id;
            $query->where('company_id', $user->company_id);
        } else {
            // Tài khoản Admin: hiển thị full và có bộ lọc theo từng CTY
            if ($companyId && $companyId !== 'all') {
                $query->where('company_id', $companyId);
            }
        }

        if ($type && in_array($type, ['supplier', 'customer', 'both'])) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tax_code', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('representative', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $partners = $query->orderBy('name')->get();

        // Danh sách CTY của tôi
        $myCompanies = Company::withCount(['partners', 'debts', 'invoices'])
            ->orderBy('name')
            ->get();

        // Thống kê nhanh theo phạm vi quyền hạn / bộ lọc
        $statsBase = Partner::query();
        if (!$isAdmin && $user && $user->company_id) {
            $statsBase->where('company_id', $user->company_id);
        } elseif ($companyId && $companyId !== 'all') {
            $statsBase->where('company_id', $companyId);
        }

        $totalPartners = (clone $statsBase)->count();
        $supplierCount = (clone $statsBase)->where('type', 'supplier')->count();
        $customerCount = (clone $statsBase)->where('type', 'customer')->count();
        $bothCount     = (clone $statsBase)->where('type', 'both')->count();

        // Biến alias cho tương thích
        $companies = $partners;

        return view('company.index', compact(
            'partners',
            'companies',
            'myCompanies',
            'type',
            'search',
            'companyId',
            'totalPartners',
            'supplierCount',
            'customerCount',
            'bothCount'
        ));
    }

    /**
     * Thêm Doanh nghiệp / Đối tác mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|in:supplier,customer,both',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        if (empty($validated['type'])) {
            $validated['type'] = 'both';
        }

        // Tài khoản CTY chỉ được lưu vào chính CTY của mình
        $user = Auth::user();
        if ($user && !$user->isAdmin() && $user->company_id) {
            $validated['company_id'] = $user->company_id;
        }

        $partner = Partner::create($validated);

        return redirect()->back()->with('success', "Đã thêm doanh nghiệp [{$partner->name}] thành công!");
    }

    /**
     * Cập nhật thông tin Doanh nghiệp / Đối tác
     */
    public function update(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);

        $user = Auth::user();
        if ($user && !$user->isAdmin() && $user->company_id) {
            if ($partner->company_id != $user->company_id) {
                abort(403, 'Bạn không có quyền chỉnh sửa doanh nghiệp này.');
            }
            $validated['company_id'] = $user->company_id;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|in:supplier,customer,both',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        if (empty($validated['type'])) {
            $validated['type'] = 'both';
        }

        if ($user && !$user->isAdmin() && $user->company_id) {
            $validated['company_id'] = $user->company_id;
        }

        $partner->update($validated);

        return redirect()->back()->with('success', "Cập nhật doanh nghiệp [{$partner->name}] thành công!");
    }

    /**
     * Xóa Doanh nghiệp / Đối tác
     */
    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);

        $user = Auth::user();
        if ($user && !$user->isAdmin() && $user->company_id) {
            if ($partner->company_id != $user->company_id) {
                abort(403, 'Bạn không có quyền xóa doanh nghiệp này.');
            }
        }

        $name = $partner->name;
        $partner->delete();

        return redirect()->back()->with('success', "Đã xóa doanh nghiệp [{$name}]!");
    }

    /**
     * Trang Quản Lý CTY riêng biệt dành cho Role Admin
     */
    public function myCompaniesIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập trang quản lý CTY.');
        }

        $search = $request->get('search');
        $query = Company::with(['users'])
            ->withCount(['partners', 'invoices', 'users']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tax_code', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('representative', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')->get();
        $totalCompanies = Company::count();
        $totalUsers = \App\Models\User::whereNotNull('company_id')->count();
        $totalInvoices = \App\Models\Invoice::count();
        $totalPartners = \App\Models\Partner::count();

        return view('company.my_companies', compact(
            'companies',
            'search',
            'totalCompanies',
            'totalUsers',
            'totalInvoices',
            'totalPartners'
        ));
    }

    /**
     * Thêm CTY của tôi
     */
    public function storeMyCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Company::create($validated);

        return redirect()->back()->with('success', "Đã thêm CTY [{$validated['name']}] thành công!");
    }

    /**
     * Cập nhật CTY của tôi
     */
    public function updateMyCompany(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_code' => 'nullable|string|max:50',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', "Cập nhật CTY [{$company->name}] thành công!");
    }

    /**
     * Xóa CTY của tôi
     */
    public function destroyMyCompany($id)
    {
        $company = Company::findOrFail($id);
        $name = $company->name;
        $company->delete();

        return redirect()->back()->with('success', "Đã xóa CTY [{$name}]!");
    }

    /**
     * Lấy danh sách file đính kèm của doanh nghiệp (AJAX)
     */
    public function getAttachments($id)
    {
        $partner = Partner::with('attachments')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($partner->company_id && $partner->company_id != $currentUser->company_id) {
                return response()->json(['error' => 'Bạn không có quyền truy cập doanh nghiệp này.'], 403);
            }
        }

        $attachments = $partner->attachments->map(function ($att) {
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
                'view_url' => route('partner.attachments.view', $att->id),
                'download_url' => route('partner.attachments.download', $att->id),
            ];
        });

        return response()->json([
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'code' => $partner->code,
                'tax_code' => $partner->tax_code,
                'representative' => $partner->representative,
                'phone' => $partner->phone,
            ],
            'attachments' => $attachments,
            'count' => $attachments->count(),
        ]);
    }

    /**
     * Tải file đính kèm lên doanh nghiệp
     */
    public function uploadAttachments(Request $request, $id)
    {
        $partner = Partner::findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($partner->company_id && $partner->company_id != $currentUser->company_id) {
                return response()->json(['error' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
            }
        }

        $validated = $request->validate([
            'category' => 'required|in:license,contract,profile,catalogue,doc',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:20480', // tối đa 20MB / file
            'notes' => 'nullable|string|max:255',
        ], [
            'category.required' => 'Vui lòng chọn phân loại tài liệu.',
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

            PartnerAttachment::create([
                'partner_id' => $partner->id,
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
                'message' => "Đã đính kèm thành công {$uploadedCount} file vào doanh nghiệp [{$partner->name}]!",
                'count' => $partner->attachments()->count(),
            ]);
        }

        return redirect()->back()->with('success', "Đã đính kèm thành công {$uploadedCount} file vào doanh nghiệp [{$partner->name}]!");
    }

    /**
     * Xem trực tiếp file đính kèm của doanh nghiệp (Ảnh / PDF)
     */
    public function viewAttachment($id)
    {
        $attachment = PartnerAttachment::with('partner')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->partner && $attachment->partner->company_id && $attachment->partner->company_id != $currentUser->company_id) {
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
        $attachment = PartnerAttachment::with('partner')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->partner && $attachment->partner->company_id && $attachment->partner->company_id != $currentUser->company_id) {
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
     * Xóa file đính kèm của doanh nghiệp
     */
    public function deleteAttachment($id)
    {
        $attachment = PartnerAttachment::with('partner')->findOrFail($id);

        $currentUser = Auth::user();
        if ($currentUser && !$currentUser->isAdmin()) {
            if ($attachment->partner && $attachment->partner->company_id && $attachment->partner->company_id != $currentUser->company_id) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['error' => 'Bạn không có quyền xóa file này.'], 403);
                }
                abort(403, 'Bạn không có quyền xóa file này.');
            }
        }

        $fileName = $attachment->file_name;
        $partnerId = $attachment->partner_id;
        $attachment->delete();

        if (request()->ajax() || request()->wantsJson()) {
            $remainingCount = PartnerAttachment::where('partner_id', $partnerId)->count();
            return response()->json([
                'success' => true,
                'message' => "Đã xóa file [{$fileName}] thành công!",
                'count' => $remainingCount,
            ]);
        }

        return redirect()->back()->with('success', "Đã xóa file [{$fileName}] thành công!");
    }
}
