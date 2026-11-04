@extends('layouts.app')

@section('title', 'Quản Lý Danh Sách Công Ty (CTY Hệ Thống)')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header trang Quản Lý CTY -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-buildings-fill text-primary"></i> Quản Lý Danh Sách Công Ty (CTY Hệ Thống)
            </h4>
            <div class="text-muted small">
                Quản lý các công ty/doanh nghiệp trực thuộc quản lý của bạn, cấp tài khoản cho từng công ty và theo dõi dữ liệu hóa đơn, công nợ
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Nút Thêm CTY Mới -->
            <button type="button" class="btn btn-theme-primary rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddMyCompany">
                <i class="bi bi-plus-circle-fill"></i>
                <span>+ Thêm CTY Mới</span>
            </button>
        </div>
    </div>

    <!-- Thống kê tổng quan CTY -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100" style="border-left: 0.25rem solid var(--blue-primary) !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tổng số CTY</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalCompanies) }}</div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-buildings fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100" style="border-left: 0.25rem solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tài khoản CTY đã cấp</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ number_format($totalUsers) }}</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100" style="border-left: 0.25rem solid #f59e0b !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tổng Hóa Đơn</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($totalInvoices) }}</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-inline-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                        <i class="bi bi-receipt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm bg-white p-3 h-100" style="border-left: 0.25rem solid #6366f1 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tổng Đối Tác</div>
                        <div class="fs-4 fw-bold text-indigo mt-1">{{ number_format($totalPartners) }}</div>
                    </div>
                    <div class="rounded-circle bg-indigo-subtle text-indigo p-3 d-inline-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem; background-color: #e0e7ff; color: #4338ca;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thanh Tìm Kiếm CTY -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3">
            <form action="{{ route('my-company.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-3 shadow-none" placeholder="Tìm tên CTY, MST, mã CTY, đại diện, SĐT..." value="{{ $search ?? '' }}">
                        <button class="btn btn-soft-primary px-3 {{ empty($search) ? 'rounded-end-pill' : '' }}" type="submit">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('my-company.index') }}" class="btn btn-outline-secondary rounded-end-pill px-2 d-flex align-items-center" title="Bỏ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách CTY -->
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 25%;">Tên Công Ty (CTY)</th>
                        <th style="width: 12%;">Mã / MST</th>
                        <th style="width: 18%;">Đại Diện / Chức Vụ</th>
                        <th style="width: 18%;">Liên Hệ & Địa Chỉ</th>
                        <th style="width: 15%;">Tài Khoản Được Gán</th>
                        <th class="text-center" style="width: 8%;">Thống Kê</th>
                        <th class="text-end" style="width: 10%;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <!-- Tên CTY -->
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="rounded-3 bg-primary-subtle text-primary p-2 mt-1 d-inline-flex align-items-center justify-content-center" style="width: 2.2rem; height: 2.2rem;">
                                        <i class="bi bi-building fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $company->name }}</div>
                                        @if($company->notes)
                                            <div class="text-muted small fst-italic text-truncate" style="max-width: 18rem;" title="{{ $company->notes }}">
                                                "{{ $company->notes }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Mã / MST -->
                            <td>
                                <div>
                                    @if($company->code)
                                        <span class="badge bg-light text-secondary border rounded-pill mb-1">{{ $company->code }}</span>
                                    @endif
                                    <div class="font-monospace small text-dark fw-medium">
                                        <i class="bi bi-file-earmark-medical text-primary me-1"></i>{{ $company->tax_code ?: '---' }}
                                    </div>
                                </div>
                            </td>

                            <!-- Đại diện / Chức vụ -->
                            <td>
                                <div class="small">
                                    <div class="fw-semibold text-dark">
                                        <i class="bi bi-person text-secondary me-1"></i>{{ $company->representative ?: 'Chưa cập nhật' }}
                                    </div>
                                    @if($company->position)
                                        <div class="badge badge-soft-info py-0 px-2 rounded-pill mt-1" style="font-size: 0.72rem;">
                                            {{ $company->position }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- SĐT & Địa chỉ -->
                            <td>
                                <div class="small">
                                    @if($company->phone)
                                        <div class="font-monospace text-primary fw-medium mb-1">
                                            <i class="bi bi-telephone text-secondary me-1"></i>{{ $company->phone }}
                                        </div>
                                    @endif
                                    @if($company->address)
                                        <div class="text-muted text-truncate" style="max-width: 16rem;" title="{{ $company->address }}">
                                            <i class="bi bi-geo-alt text-secondary me-1"></i>{{ $company->address }}
                                        </div>
                                    @else
                                        <span class="text-muted">Chưa có địa chỉ</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tài khoản đăng nhập gán cho CTY -->
                            <td>
                                <div class="d-flex flex-wrap align-items-center gap-1">
                                    @forelse($company->users as $u)
                                        <span class="badge bg-light text-dark border rounded-pill py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-fill text-primary"></i> {{ $u->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted small fst-italic">Chưa có tài khoản</span>
                                    @endforelse
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 mt-1" style="font-size: 0.7rem;" onclick="openCreateUserForCompany({{ $company->id }}, '{{ addslashes($company->name) }}')">
                                        <i class="bi bi-plus"></i> Cấp nick
                                    </button>
                                </div>
                            </td>

                            <!-- Thống kê HĐ & Đối tác -->
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center gap-1 small">
                                    <a href="{{ route('invoice.in', ['company_id' => $company->id]) }}" class="badge badge-soft-warning py-1 px-2 rounded-pill text-decoration-none" title="Xem hóa đơn">
                                        {{ $company->invoices_count }} HĐ
                                    </a>
                                    <a href="{{ route('company.index', ['company_id' => $company->id]) }}" class="badge badge-soft-info py-1 px-2 rounded-pill text-decoration-none" title="Xem đối tác">
                                        {{ $company->partners_count }} Đối tác
                                    </a>
                                </div>
                            </td>

                            <!-- Thao tác -->
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Nút Sửa -->
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-1" style="width: 2rem; height: 2rem;" title="Chỉnh sửa CTY" onclick="openEditMyCompanyModal({{ json_encode($company) }})">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- Nút Xóa -->
                                    <form action="{{ route('my-company.destroy', $company->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa CTY [{{ addslashes($company->name) }}]? Các hóa đơn, công nợ và đối tác thuộc CTY này sẽ bị ảnh hưởng.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 2rem; height: 2rem;" title="Xóa CTY">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-buildings fs-1 opacity-50 text-secondary"></i>
                                    <div class="mt-2 fw-medium">Chưa có công ty nào trong hệ thống</div>
                                    <p class="small text-muted mb-3">Hãy tạo công ty đầu tiên để quản lý hóa đơn và phân quyền cho nhân viên.</p>
                                    <button type="button" class="btn btn-theme-primary btn-sm rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAddMyCompany">
                                        <i class="bi bi-plus-circle-fill me-1"></i> Thêm CTY Ngay
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL THÊM CTY MỚI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAddMyCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-building-add text-primary"></i>
                    <span>Thêm Công Ty Mới (CTY Hệ Thống)</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('my-company.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Tên Công Ty (CTY) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="VD: Công ty TNHH Cát Vượng, Cty Xây Dựng Á Đông..." required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã Viết Tắt</label>
                            <input type="text" name="code" class="form-control rounded-3 shadow-none" placeholder="VD: CATVUONG, ADONG...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã Số Thuế (MST)</label>
                            <input type="text" name="tax_code" class="form-control rounded-3 shadow-none font-monospace" placeholder="Mã số thuế doanh nghiệp...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Điện Thoại</label>
                            <input type="text" name="phone" class="form-control rounded-3 shadow-none font-monospace" placeholder="Số điện thoại liên hệ...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Người Đại Diện</label>
                            <input type="text" name="representative" class="form-control rounded-3 shadow-none" placeholder="Họ và tên người đại diện...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Chức Vụ</label>
                            <input type="text" name="position" class="form-control rounded-3 shadow-none" placeholder="VD: Giám đốc, Kế toán trưởng...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Địa Chỉ Trụ Sở</label>
                            <input type="text" name="address" class="form-control rounded-3 shadow-none" placeholder="Địa chỉ đăng ký kinh doanh...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi Chú</label>
                            <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Thông tin bổ sung nếu có..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu CTY Mới
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SỬA CTY -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditMyCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Chỉnh Sửa Thông Tin CTY <span id="edit_company_display_name" class="text-primary font-monospace"></span></span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMyCompanyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Tên Công Ty (CTY) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="edit_my_comp_name" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã Viết Tắt</label>
                            <input type="text" name="code" id="edit_my_comp_code" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã Số Thuế (MST)</label>
                            <input type="text" name="tax_code" id="edit_my_comp_tax_code" class="form-control rounded-3 shadow-none font-monospace">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Điện Thoại</label>
                            <input type="text" name="phone" id="edit_my_comp_phone" class="form-control rounded-3 shadow-none font-monospace">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Người Đại Diện</label>
                            <input type="text" name="representative" id="edit_my_comp_representative" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Chức Vụ</label>
                            <input type="text" name="position" id="edit_my_comp_position" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Địa Chỉ Trụ Sở</label>
                            <input type="text" name="address" id="edit_my_comp_address" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi Chú</label>
                            <textarea name="notes" id="edit_my_comp_notes" class="form-control rounded-3 shadow-none" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL CẤP TÀI KHOẢN NHANH CHO CTY -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCreateUserForCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 fs-6">
                    <i class="bi bi-person-plus text-primary"></i>
                    <span>Cấp Tài Khoản Cho: <strong id="assign_company_name" class="text-primary"></strong></span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="company_id" id="assign_company_id" value="">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                Tên đăng nhập (Tài khoản) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: cty_catvuong, ketoan_adong..." required autocomplete="off">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-control rounded-3 shadow-none" placeholder="Tối thiểu 3 ký tự..." required autocomplete="new-password">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3 shadow-none" placeholder="Nhập lại mật khẩu..." required autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Tạo Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openEditMyCompanyModal(item) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditMyCompany'));
        const form = document.getElementById('editMyCompanyForm');
        form.action = `/my-companies/${item.id}`;

        document.getElementById('edit_company_display_name').textContent = `[${item.name}]`;
        document.getElementById('edit_my_comp_name').value = item.name || '';
        document.getElementById('edit_my_comp_code').value = item.code || '';
        document.getElementById('edit_my_comp_tax_code').value = item.tax_code || '';
        document.getElementById('edit_my_comp_phone').value = item.phone || '';
        document.getElementById('edit_my_comp_representative').value = item.representative || '';
        document.getElementById('edit_my_comp_position').value = item.position || '';
        document.getElementById('edit_my_comp_address').value = item.address || '';
        document.getElementById('edit_my_comp_notes').value = item.notes || '';

        modal.show();
    }

    function openCreateUserForCompany(companyId, companyName) {
        document.getElementById('assign_company_id').value = companyId;
        document.getElementById('assign_company_name').textContent = companyName;
        const modal = new bootstrap.Modal(document.getElementById('modalCreateUserForCompany'));
        modal.show();
    }
</script>
@endpush
@endsection

