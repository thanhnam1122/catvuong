@extends('layouts.app')

@section('title', 'Quản Lý Doanh Nghiệp (Nhà Cung Cấp / Đối Tác)')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header trang Doanh Nghiệp -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-buildings text-primary"></i> Quản Lý Doanh Nghiệp (Nhà Cung Cấp / Đối Tác)
            </h4>
            <div class="text-muted small">
                Quản lý hồ sơ doanh nghiệp đối tác, nhà cung cấp, khách hàng; mã số thuế, đại diện pháp luật và thông tin liên hệ
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(Auth::check() && Auth::user()->isAdmin())
                <!-- Nút Quản Lý CTY Của Tôi (Chỉ Quản trị viên) -->
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalMyCompanies">
                    <i class="bi bi-building-gear text-primary"></i>
                    <span>Quản Lý CTY Của Tôi ({{ $myCompanies->count() }})</span>
                </button>
            @endif

            <!-- Nút Thêm Doanh Nghiệp Mới -->
            <button type="button" class="btn btn-theme-primary rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCompany">
                <i class="bi bi-plus-circle-fill"></i>
                <span>+ Thêm Doanh Nghiệp Mới</span>
            </button>
        </div>
    </div>

    <!-- Thanh Lọc & Tìm Kiếm Doanh Nghiệp -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('company.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="type" value="{{ $type ?? 'all' }}">

                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Bộ lọc theo từng CTY (Chỉ hiển thị cho Quản trị viên) -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-pill">
                                <i class="bi bi-building text-primary"></i>
                            </span>
                            <select name="company_id" class="form-select form-select-sm rounded-end-pill shadow-none" onchange="this.form.submit()">
                                <option value="all" {{ ($companyId == 'all' || empty($companyId)) ? 'selected' : '' }}>
                                    🏢 Tất cả CTY ({{ $myCompanies->count() }})
                                </option>
                                @foreach($myCompanies as $mc)
                                    <option value="{{ $mc->id }}" {{ $companyId == $mc->id ? 'selected' : '' }}>
                                        {{ $mc->name }} {{ $mc->code ? '('.$mc->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <!-- Phân loại đối tác tab-filter -->
                <div class="col-12 {{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-md-8 col-lg-5' : 'col-12 col-lg-7' }}">
                    <div class="btn-group btn-group-sm p-1 rounded-pill flex-wrap" style="background-color: #f1f5f9;" role="group">
                        <a href="{{ route('company.index', ['type' => 'all', 'search' => $search, 'company_id' => $companyId]) }}" class="btn rounded-pill px-3 {{ empty($type) || $type == 'all' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                            Tất cả ({{ $totalPartners }})
                        </a>
                        <a href="{{ route('company.index', ['type' => 'supplier', 'search' => $search, 'company_id' => $companyId]) }}" class="btn rounded-pill px-3 {{ $type == 'supplier' ? 'btn-white bg-white text-warning shadow-sm fw-semibold' : 'text-muted border-0' }}" style="{{ $type == 'supplier' ? 'color: #d97706 !important;' : '' }}">
                            📥 Nhà Cung Cấp ({{ $supplierCount }})
                        </a>
                        <a href="{{ route('company.index', ['type' => 'customer', 'search' => $search, 'company_id' => $companyId]) }}" class="btn rounded-pill px-3 {{ $type == 'customer' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                            📤 Khách Hàng ({{ $customerCount }})
                        </a>
                        <a href="{{ route('company.index', ['type' => 'both', 'search' => $search, 'company_id' => $companyId]) }}" class="btn rounded-pill px-3 {{ $type == 'both' ? 'btn-white bg-white text-success shadow-sm fw-semibold' : 'text-muted border-0' }}">
                            🔄 Cả Hai Chiều ({{ $bothCount }})
                        </a>
                    </div>
                </div>

                <!-- Ô tìm kiếm -->
                <div class="col-12 {{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-12 col-lg-4' : 'col-12 col-lg-5' }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-3 shadow-none" placeholder="Tìm tên doanh nghiệp, MST, SĐT, đại diện..." value="{{ $search ?? '' }}">
                        <button class="btn btn-soft-primary px-3 {{ (empty($search) && (empty($companyId) || $companyId == 'all') && (empty($type) || $type == 'all')) ? 'rounded-end-pill' : '' }}" type="submit">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                        @if(!empty($search) || (!empty($type) && $type != 'all') || (!empty($companyId) && $companyId != 'all'))
                            <a href="{{ route('company.index') }}" class="btn btn-outline-secondary rounded-end-pill px-2 d-flex align-items-center" title="Đặt lại bộ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách thẻ thông tin doanh nghiệp (Profile Card View) -->
    <div class="row g-3 mb-4">
        @forelse($partners as $partner)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header thẻ: Tên doanh nghiệp, Loại đối tác & Nút menu -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="navbar-brand-badge rounded-3 flex-shrink-0" style="width: 2.75rem; height: 2.75rem; font-size: 1.35rem;">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 fs-6">{{ $partner->name }}</h5>
                                        <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                            @if($partner->code)
                                                <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.7rem;">
                                                    Mã: {{ $partner->code }}
                                                </span>
                                            @endif

                                            @if($partner->type === 'supplier')
                                                <span class="badge badge-soft-warning py-1 px-2 rounded-pill" style="font-size: 0.7rem;">
                                                    <i class="bi bi-box-arrow-in-down-left me-1"></i> Nhà Cung Cấp
                                                </span>
                                            @elseif($partner->type === 'customer')
                                                <span class="badge badge-soft-info py-1 px-2 rounded-pill" style="font-size: 0.7rem;">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Khách Hàng
                                                </span>
                                            @else
                                                <span class="badge badge-soft-success py-1 px-2 rounded-pill" style="font-size: 0.7rem;">
                                                    <i class="bi bi-arrow-left-right me-1"></i> Đối Tác 2 Chiều
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle shadow-none p-1" style="width: 2rem; height: 2rem;" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2">
                                        <li>
                                            <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openPartnerAttachmentsModal({{ $partner->id }}, '{{ addslashes($partner->name) }}')">
                                                <i class="bi bi-paperclip me-2 text-primary"></i> Đính kèm file ({{ $partner->attachments_count ?? 0 }})
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openEditCompanyModal({{ json_encode($partner) }})">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Sửa thông tin
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('company.destroy', $partner->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa doanh nghiệp [{{ $partner->name }}] không?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item small text-danger py-2 rounded-3">
                                                    <i class="bi bi-trash3 me-2"></i> Xóa doanh nghiệp
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Khối hiển thị thông tin doanh nghiệp chi tiết (MST, Người đại diện, Chức vụ, SĐT, Địa chỉ) -->
                            <div class="p-3 rounded-4 mb-3" style="background-color: #f8fafc; border: 0.0625rem solid #e2e8f0;">
                                <!-- 1. Mã số thuế (MST) -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-file-earmark-medical text-primary me-1"></i> Mã số thuế (MST):
                                    </span>
                                    <span class="fw-bold text-dark font-monospace fs-6">
                                        {{ $partner->tax_code ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 2. Người đại diện -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-person-badge text-primary me-1"></i> Người đại diện:
                                    </span>
                                    <span class="fw-semibold text-dark">
                                        {{ $partner->representative ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 3. Chức vụ -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-award text-primary me-1"></i> Chức vụ:
                                    </span>
                                    <span class="badge badge-soft-info py-1 px-2 rounded-pill">
                                        {{ $partner->position ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 4. Số điện thoại & Email -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-telephone text-primary me-1"></i> Số điện thoại:
                                    </span>
                                    <span class="fw-bold text-primary font-monospace">
                                        {{ $partner->phone ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                @if($partner->email)
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span class="text-muted small fw-medium">
                                            <i class="bi bi-envelope text-primary me-1"></i> Email:
                                        </span>
                                        <span class="text-dark small">
                                            {{ $partner->email }}
                                        </span>
                                    </div>
                                @endif

                                <!-- 5. Địa chỉ trụ sở -->
                                <div class="pt-1">
                                    <span class="text-muted small fw-medium d-block mb-1">
                                        <i class="bi bi-geo-alt text-primary me-1"></i> Địa chỉ:
                                    </span>
                                    <div class="text-dark small fw-medium ps-3 border-start border-2 border-primary">
                                        {{ $partner->address ?? 'Chưa cập nhật địa chỉ' }}
                                    </div>
                                </div>
                            </div>

                            @if(Auth::check() && Auth::user()->isAdmin() && $partner->company)
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-building me-1 text-secondary"></i> Giao dịch với CTY: <strong>{{ $partner->company->name }}</strong>
                                </div>
                            @endif

                            @if($partner->notes)
                                <div class="small text-muted fst-italic mb-3 ps-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i> "{{ $partner->notes }}"
                                </div>
                            @endif
                        </div>

                        <!-- Nút Quản lý Hồ sơ & File đính kèm của doanh nghiệp -->
                        <div class="mb-2">
                            <button type="button" 
                                    class="btn btn-sm {{ ($partner->attachments_count ?? 0) > 0 ? 'btn-soft-primary' : 'btn-light border text-secondary' }} rounded-pill w-100 py-1 d-flex align-items-center justify-content-between px-3 text-decoration-none shadow-none" 
                                    onclick="openPartnerAttachmentsModal({{ $partner->id }}, '{{ addslashes($partner->name) }}')"
                                    title="Xem và đính kèm giấy phép ĐKKD, hợp đồng nguyên tắc, catalogue, báo giá...">
                                <span class="d-inline-flex align-items-center gap-2 small fw-semibold">
                                    <i class="bi bi-paperclip text-primary fs-6"></i>
                                    <span>Hồ sơ &amp; File đính kèm</span>
                                </span>
                                @if(($partner->attachments_count ?? 0) > 0)
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-file-earmark-check-fill me-1"></i>{{ $partner->attachments_count }} file
                                    </span>
                                @else
                                    <span class="badge bg-white text-muted border rounded-pill px-2 py-0" style="font-size: 0.68rem;">
                                        <i class="bi bi-plus-lg me-1"></i>+ File
                                    </span>
                                @endif
                            </button>
                        </div>

                        <!-- Các nút liên kết hóa đơn đầu vào & hóa đơn xuất ra -->
                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="{{ route('invoice.in', ['partner_id' => $partner->id]) }}" class="btn btn-soft-primary btn-sm rounded-pill flex-grow-1 text-center py-2" title="Xem hóa đơn đầu vào từ nhà cung cấp này">
                                <i class="bi bi-box-arrow-in-down-left me-1"></i> HĐ Đầu Vào
                            </a>
                            <a href="{{ route('invoice.out', ['partner_id' => $partner->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 text-center py-2" title="Xem hóa đơn xuất ra cho đối tác này">
                                <i class="bi bi-box-arrow-up-right me-1"></i> HĐ Xuất Ra
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-buildings fs-1 text-secondary opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark">Chưa có doanh nghiệp nào</h5>
                <p class="text-muted small">Hãy thêm thông tin nhà cung cấp / đối tác bạn đang làm việc.</p>
                <button type="button" class="btn btn-theme-primary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAddCompany">
                    + Thêm Doanh Nghiệp Đầu Tiên
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL THÊM DOANH NGHIỆP MỚI (Nhà Cung Cấp / Đối Tác) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAddCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-building-add text-primary"></i>
                    <span>Thêm Doanh Nghiệp Mới</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('company.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Tên doanh nghiệp -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Tên doanh nghiệp / Đối tác / Nhà cung cấp <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Công ty TNHH Cung Ứng Vật Tư..." required>
                    </div>

                    <!-- Phân loại & CTY giao dịch -->
                    <div class="row g-2 mb-3">
                        <div class="{{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-6' : 'col-12' }}">
                            <label class="form-label fw-semibold text-dark small mb-1">Phân loại <span class="text-danger">*</span></label>
                            <select name="type" class="form-select rounded-3 shadow-none">
                                <option value="supplier" {{ request('type') == 'supplier' ? 'selected' : '' }}>📥 Nhà cung cấp (Mua hàng)</option>
                                <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>📤 Khách hàng (Bán hàng)</option>
                                <option value="both" {{ empty(request('type')) || request('type') == 'all' || request('type') == 'both' ? 'selected' : '' }}>🔄 Cả hai chiều</option>
                            </select>
                        </div>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Giao dịch với CTY</label>
                                <select name="company_id" class="form-select rounded-3 shadow-none">
                                    <option value="">Dùng chung tất cả CTY</option>
                                    @foreach($myCompanies as $mc)
                                        <option value="{{ $mc->id }}" {{ $companyId == $mc->id ? 'selected' : '' }}>{{ $mc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        @endif
                    </div>

                    <!-- MST & Mã viết tắt -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã số thuế (MST)</label>
                            <input type="text" name="tax_code" class="form-control rounded-3 shadow-none font-monospace" placeholder="VD: 0109887766">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã viết tắt</label>
                            <input type="text" name="code" class="form-control rounded-3 shadow-none" placeholder="VD: NCC-01">
                        </div>
                    </div>

                    <!-- Người đại diện & Chức vụ -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Người đại diện pháp luật</label>
                            <input type="text" name="representative" class="form-control rounded-3 shadow-none" placeholder="Họ và tên người đại diện...">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Chức vụ</label>
                            <input type="text" name="position" class="form-control rounded-3 shadow-none" placeholder="VD: Giám Đốc...">
                        </div>
                    </div>

                    <!-- Số điện thoại & Email -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" class="form-control rounded-3 shadow-none" placeholder="Số hotline hoặc di động...">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 shadow-none" placeholder="lienhe@domain.com">
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Địa chỉ trụ sở</label>
                        <input type="text" name="address" class="form-control rounded-3 shadow-none" placeholder="Địa chỉ đăng ký kinh doanh...">
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Ghi chú thêm</label>
                        <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Thông tin ngân hàng, ghi chú hợp đồng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Doanh Nghiệp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SỬA DOANH NGHIỆP -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Chỉnh Sửa Doanh Nghiệp</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCompanyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <!-- Tên doanh nghiệp -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Tên doanh nghiệp / Đối tác <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_company_name" class="form-control rounded-3 shadow-none" required>
                    </div>

                    <!-- Phân loại & CTY giao dịch -->
                    <div class="row g-2 mb-3">
                        <div class="{{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-6' : 'col-12' }}">
                            <label class="form-label fw-semibold text-dark small mb-1">Phân loại <span class="text-danger">*</span></label>
                            <select name="type" id="edit_company_type" class="form-select rounded-3 shadow-none">
                                <option value="supplier">📥 Nhà cung cấp (Mua hàng)</option>
                                <option value="customer">📤 Khách hàng (Bán hàng)</option>
                                <option value="both">🔄 Cả hai chiều</option>
                            </select>
                        </div>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <div class="col-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Giao dịch với CTY</label>
                                <select name="company_id" id="edit_company_company_id" class="form-select rounded-3 shadow-none">
                                    <option value="">Dùng chung tất cả CTY</option>
                                    @foreach($myCompanies as $mc)
                                        <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="company_id" id="edit_company_company_id" value="{{ Auth::user()->company_id }}">
                        @endif
                    </div>

                    <!-- MST & Mã viết tắt -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã số thuế (MST)</label>
                            <input type="text" name="tax_code" id="edit_company_tax_code" class="form-control rounded-3 shadow-none font-monospace">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã viết tắt</label>
                            <input type="text" name="code" id="edit_company_code" class="form-control rounded-3 shadow-none">
                        </div>
                    </div>

                    <!-- Người đại diện & Chức vụ -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Người đại diện pháp luật</label>
                            <input type="text" name="representative" id="edit_company_representative" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Chức vụ</label>
                            <input type="text" name="position" id="edit_company_position" class="form-control rounded-3 shadow-none">
                        </div>
                    </div>

                    <!-- Số điện thoại & Email -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại liên hệ</label>
                            <input type="text" name="phone" id="edit_company_phone" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Email</label>
                            <input type="email" name="email" id="edit_company_email" class="form-control rounded-3 shadow-none">
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Địa chỉ trụ sở</label>
                        <input type="text" name="address" id="edit_company_address" class="form-control rounded-3 shadow-none">
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Ghi chú & thông tin thêm</label>
                        <textarea name="notes" id="edit_company_notes" class="form-control rounded-3 shadow-none" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL QUẢN LÝ CTY CỦA TÔI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalMyCompanies" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-building-gear text-primary"></i>
                    <span>Danh Sách CTY Của Tôi (Đơn Vị Quản Lý)</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-3" style="background-color: var(--blue-soft-50); color: var(--blue-dark);">
                    <i class="bi bi-info-circle me-1"></i> Đây là danh sách các Công ty (CTY) của bạn. Cột này sẽ hiển thị là <strong>CTY</strong> trong các sổ hóa đơn.
                </div>

                <!-- Bảng danh sách CTY của tôi -->
                <div class="table-responsive mb-4">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tên CTY</th>
                                <th>Mã</th>
                                <th>MST</th>
                                <th>Đại Diện</th>
                                <th>SĐT</th>
                                <th class="text-end">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myCompanies as $mc)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $mc->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border rounded-pill">{{ $mc->code ?? '---' }}</span>
                                    </td>
                                    <td class="font-monospace small">{{ $mc->tax_code ?? '---' }}</td>
                                    <td class="small">{{ $mc->representative ?? '---' }}</td>
                                    <td class="small font-monospace">{{ $mc->phone ?? '---' }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('my-company.destroy', $mc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa CTY [{{ $mc->name }}]? Các hóa đơn liên quan có thể bị ảnh hưởng.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 1.85rem; height: 1.85rem;" title="Xóa CTY">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">Chưa có CTY nào. Hãy thêm CTY đầu tiên bên dưới.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Form Thêm CTY Mới Nhanh -->
                <div class="card border rounded-4 bg-light p-3">
                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-plus-circle text-primary"></i> Thêm CTY Mới Của Bạn:
                    </div>
                    <form action="{{ route('my-company.store') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12 col-md-5">
                            <input type="text" name="name" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Tên CTY (VD: Công ty TNHH Cát Vượng...)" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="text" name="code" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Mã CTY">
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="text" name="tax_code" class="form-control form-control-sm rounded-3 shadow-none font-monospace" placeholder="Mã số thuế">
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-theme-primary btn-sm w-100 rounded-pill">
                                + Thêm CTY
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL QUẢN LÝ FILE ĐÍNH KÈM DOANH NGHIỆP -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPartnerAttachments" tabindex="-1" aria-labelledby="modalPartnerAttachmentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: var(--blue-soft-50);">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 2.2rem; height: 2.2rem; font-size: 1rem;">
                        <i class="bi bi-paperclip"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="modalPartnerAttachmentsLabel">
                            Hồ Sơ &amp; File Đính Kèm Doanh Nghiệp <span id="partnerAttModalName" class="text-primary font-monospace"></span>
                        </h5>
                        <div class="text-muted" style="font-size: 0.75rem;" id="partnerAttModalSub">
                            Lưu trữ giấy phép ĐKKD, hợp đồng nguyên tắc, hồ sơ năng lực, catalogue, báo giá...
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Khu vực Tải file mới lên -->
                <div class="card border rounded-3 mb-4 shadow-none" style="background-color: #f8fafc; border-color: var(--border-soft) !important;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                            <i class="bi bi-cloud-arrow-up-fill text-primary"></i>
                            <span>Tải thêm file đính kèm mới</span>
                        </h6>
                        <form id="uploadPartnerAttachmentForm" method="POST" enctype="multipart/form-data" onsubmit="handleUploadPartnerAttachments(event)">
                            @csrf
                            <input type="hidden" id="partner_att_partner_id" name="partner_id" value="">
                            
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-dark mb-1">
                                        Phân loại tài liệu <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" id="partner_att_category" class="form-select form-select-sm rounded-3 shadow-none" required>
                                        <option value="license">📜 Giấy phép ĐKKD (Đăng ký kinh doanh)</option>
                                        <option value="contract">📑 HĐ Nguyên Tắc (Hợp đồng / Thỏa thuận)</option>
                                        <option value="profile">🏢 Hồ Sơ Năng Lực (Profile công ty)</option>
                                        <option value="catalogue">📊 Báo Giá / Catalogue sản phẩm</option>
                                        <option value="doc">📁 Tài Liệu Khác (Giấy ủy quyền / Chứng từ...)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold text-dark mb-1">
                                        Chọn file đính kèm (có thể chọn nhiều file) <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="files[]" id="partner_att_files_input" class="form-control form-control-sm rounded-3 shadow-none" multiple required accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.zip,.rar">
                                    <div class="text-muted" style="font-size: 0.72rem; margin-top: 0.2rem;">
                                        Hỗ trợ: PDF, Ảnh (JPG, PNG), Excel, Word, ZIP. Tối đa 20MB / file.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold text-dark mb-1">Ghi chú tệp (tùy chọn)</label>
                                    <input type="text" name="notes" id="partner_att_notes" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Ví dụ: Giấy phép kinh doanh năm 2026, Báo giá nguyên liệu đợt 1...">
                                </div>
                                <div class="col-12 col-md-4 text-end">
                                    <button type="submit" id="btnUploadPartnerAttachment" class="btn btn-theme-primary btn-sm rounded-pill px-3 py-1 w-100 shadow-sm d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-upload"></i>
                                        <span>+ Tải Lên Doanh Nghiệp</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabs lọc danh mục file -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="btn-group btn-group-sm p-1 rounded-pill flex-wrap" style="background-color: #f1f5f9;" role="group">
                        <button type="button" class="btn btn-sm rounded-pill px-3 partner-att-filter-btn active bg-white text-primary fw-semibold shadow-sm" onclick="filterPartnerAttachmentList('all', this)">
                            Tất cả (<span id="count_partner_att_all">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0" onclick="filterPartnerAttachmentList('license', this)">
                            📜 ĐKKD (<span id="count_partner_att_license">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0" onclick="filterPartnerAttachmentList('contract', this)">
                            📑 HĐ Nguyên Tắc (<span id="count_partner_att_contract">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0" onclick="filterPartnerAttachmentList('profile', this)">
                            🏢 Hồ Sơ NL (<span id="count_partner_att_profile">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0" onclick="filterPartnerAttachmentList('catalogue', this)">
                            📊 Báo Giá/Catalogue (<span id="count_partner_att_catalogue">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0" onclick="filterPartnerAttachmentList('doc', this)">
                            📁 Khác (<span id="count_partner_att_doc">0</span>)
                        </button>
                    </div>

                    <div class="text-muted small" style="font-size: 0.78rem;">
                        <span id="partnerAttTotalFilesNotice">0 tệp tin đính kèm</span>
                    </div>
                </div>

                <!-- Danh sách file đính kèm -->
                <div id="partnerAttListLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                    <span class="ms-2 small text-muted">Đang tải danh sách file...</span>
                </div>

                <div id="partnerAttEmptyState" class="text-center py-4 text-muted border rounded-3 bg-light d-none">
                    <i class="bi bi-folder2-open fs-2 text-secondary opacity-50 d-block mb-1"></i>
                    <span class="small">Chưa có file đính kèm nào cho doanh nghiệp này.</span>
                </div>

                <div id="partnerAttTableWrapper" class="table-responsive rounded-3 border">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Tên Tài Liệu &amp; Kích Thước</th>
                                <th style="width: 8.5rem;">Phân Loại</th>
                                <th style="width: 9rem;">Thời Gian Tải</th>
                                <th class="text-end" style="width: 8rem;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="partnerAttTableBody">
                            <!-- Danh sách load bằng AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openEditCompanyModal(item) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditCompany'));
        const form = document.getElementById('editCompanyForm');
        form.action = `/companies/${item.id}`;

        document.getElementById('edit_company_name').value = item.name || '';
        document.getElementById('edit_company_tax_code').value = item.tax_code || '';
        document.getElementById('edit_company_code').value = item.code || '';
        document.getElementById('edit_company_type').value = item.type || 'both';
        document.getElementById('edit_company_company_id').value = item.company_id || '';
        document.getElementById('edit_company_representative').value = item.representative || '';
        document.getElementById('edit_company_position').value = item.position || '';
        document.getElementById('edit_company_phone').value = item.phone || '';
        document.getElementById('edit_company_email').value = item.email || '';
        document.getElementById('edit_company_address').value = item.address || '';
        document.getElementById('edit_company_notes').value = item.notes || '';

        modal.show();
    }

    // --- LOGIC QUẢN LÝ FILE ĐÍNH KÈM DOANH NGHIỆP ---
    let currentPartnerAttachments = [];
    let currentPartnerAttachmentFilter = 'all';
    let currentActivePartnerId = null;

    function openPartnerAttachmentsModal(partnerId, partnerName) {
        currentActivePartnerId = partnerId;
        const modalEl = document.getElementById('modalPartnerAttachments');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('partnerAttModalName').textContent = `[${partnerName}]`;
        document.getElementById('partner_att_partner_id').value = partnerId;
        document.getElementById('uploadPartnerAttachmentForm').action = `/partners/${partnerId}/attachments`;
        document.getElementById('partner_att_files_input').value = '';
        document.getElementById('partner_att_notes').value = '';

        modal.show();
        loadPartnerAttachments(partnerId);
    }

    function loadPartnerAttachments(partnerId) {
        const loadingEl = document.getElementById('partnerAttListLoading');
        const tableWrapper = document.getElementById('partnerAttTableWrapper');
        const emptyState = document.getElementById('partnerAttEmptyState');

        loadingEl.classList.remove('d-none');
        tableWrapper.classList.add('d-none');
        emptyState.classList.add('d-none');

        fetch(`/partners/${partnerId}/attachments`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('d-none');
            currentPartnerAttachments = data.attachments || [];
            renderPartnerAttachmentList();
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            alert('Lỗi tải danh sách file: ' + err.message);
        });
    }

    function renderPartnerAttachmentList() {
        const tableWrapper = document.getElementById('partnerAttTableWrapper');
        const emptyState = document.getElementById('partnerAttEmptyState');
        const tbody = document.getElementById('partnerAttTableBody');

        const total = currentPartnerAttachments.length;
        const countLicense = currentPartnerAttachments.filter(a => a.category === 'license').length;
        const countContract = currentPartnerAttachments.filter(a => a.category === 'contract').length;
        const countProfile = currentPartnerAttachments.filter(a => a.category === 'profile').length;
        const countCatalogue = currentPartnerAttachments.filter(a => a.category === 'catalogue').length;
        const countDoc = currentPartnerAttachments.filter(a => a.category === 'doc').length;

        document.getElementById('count_partner_att_all').textContent = total;
        document.getElementById('count_partner_att_license').textContent = countLicense;
        document.getElementById('count_partner_att_contract').textContent = countContract;
        document.getElementById('count_partner_att_profile').textContent = countProfile;
        document.getElementById('count_partner_att_catalogue').textContent = countCatalogue;
        document.getElementById('count_partner_att_doc').textContent = countDoc;
        document.getElementById('partnerAttTotalFilesNotice').textContent = `${total} tệp tin đính kèm`;

        let filtered = currentPartnerAttachments;
        if (currentPartnerAttachmentFilter !== 'all') {
            filtered = currentPartnerAttachments.filter(a => a.category === currentPartnerAttachmentFilter);
        }

        if (filtered.length === 0) {
            tableWrapper.classList.add('d-none');
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');
        tableWrapper.classList.remove('d-none');

        tbody.innerHTML = filtered.map(att => {
            let iconClass = 'bi-file-earmark-text text-secondary';
            if (att.is_image) iconClass = 'bi-file-earmark-image text-success';
            else if (att.is_pdf) iconClass = 'bi-file-earmark-pdf text-danger';
            else if (att.file_name.endsWith('.xls') || att.file_name.endsWith('.xlsx')) iconClass = 'bi-file-earmark-spreadsheet text-success';
            else if (att.file_name.endsWith('.doc') || att.file_name.endsWith('.docx')) iconClass = 'bi-file-earmark-word text-primary';
            else if (att.file_name.endsWith('.zip') || att.file_name.endsWith('.rar')) iconClass = 'bi-file-earmark-zip text-warning';

            return `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi ${iconClass} fs-4"></i>
                            <div class="text-truncate" style="max-width: 18rem;">
                                <a href="${att.view_url}" target="_blank" class="fw-bold text-dark text-decoration-none" title="${att.file_name}">
                                    ${att.file_name}
                                </a>
                                <div class="text-muted small" style="font-size: 0.73rem;">
                                    <span>${att.file_size}</span>
                                    ${att.notes ? `• <span class="fst-italic text-secondary">${att.notes}</span>` : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        ${att.category_badge}
                    </td>
                    <td>
                        <div class="text-dark small">${att.created_at}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">${att.uploaded_by || ''}</div>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1 align-items-center">
                            <a href="${att.view_url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2" style="font-size: 0.75rem;" title="Xem file">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                            <a href="${att.download_url}" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-2" style="font-size: 0.75rem;" title="Tải file về máy">
                                <i class="bi bi-download"></i> Tải
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-2" style="font-size: 0.75rem;" title="Xóa file" onclick="deletePartnerAttachmentItem(${att.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterPartnerAttachmentList(category, btnEl) {
        currentPartnerAttachmentFilter = category;
        document.querySelectorAll('.partner-att-filter-btn').forEach(b => {
            b.className = 'btn btn-sm rounded-pill px-2 partner-att-filter-btn text-muted border-0';
        });
        btnEl.className = 'btn btn-sm rounded-pill px-3 partner-att-filter-btn active bg-white text-primary fw-semibold shadow-sm';
        renderPartnerAttachmentList();
    }

    function handleUploadPartnerAttachments(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnUploadPartnerAttachment');
        const partnerId = document.getElementById('partner_att_partner_id').value;
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tải...';

        fetch(`/partners/${partnerId}/attachments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>+ Tải Lên Doanh Nghiệp</span>';
            if (data.success) {
                document.getElementById('partner_att_files_input').value = '';
                document.getElementById('partner_att_notes').value = '';
                loadPartnerAttachments(partnerId);
            } else {
                alert(data.message || 'Lỗi khi tải file lên.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>+ Tải Lên Doanh Nghiệp</span>';
            alert('Lỗi: ' + err.message);
        });
    }

    function deletePartnerAttachmentItem(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa file đính kèm này không?')) return;

        fetch(`/partner-attachments/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadPartnerAttachments(currentActivePartnerId);
            } else {
                alert(data.error || 'Lỗi xóa file.');
            }
        })
        .catch(err => alert('Lỗi: ' + err.message));
    }
</script>
@endpush
@endsection
