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
</script>
@endpush
@endsection
