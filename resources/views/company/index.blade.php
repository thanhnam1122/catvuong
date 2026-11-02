@extends('layouts.app')

@section('title', 'Hồ Sơ Doanh Nghiệp / Đơn Vị')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header trang Doanh Nghiệp -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-buildings text-primary"></i> Hồ Sơ Doanh Nghiệp Đang Quản Lý
            </h4>
            <div class="text-muted small">
                Quản lý thông tin pháp nhân, mã số thuế, người đại diện và địa chỉ của các doanh nghiệp bạn đang phụ trách
            </div>
        </div>
        <button type="button" class="btn btn-theme-primary rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAddCompany">
            <i class="bi bi-plus-circle-fill"></i>
            <span>+ Thêm Doanh Nghiệp Mới</span>
        </button>
    </div>

    <!-- Danh sách thẻ thông tin doanh nghiệp (Profile Card View) -->
    <div class="row g-3 mb-4">
        @forelse($companies as $comp)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header thẻ: Tên doanh nghiệp & Nút menu -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="navbar-brand-badge rounded-3 flex-shrink-0" style="width: 2.75rem; height: 2.75rem; font-size: 1.35rem;">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 fs-6">{{ $comp->name }}</h5>
                                        @if($comp->code)
                                            <span class="badge bg-light text-secondary border rounded-pill mt-1" style="font-size: 0.7rem;">
                                                Mã: {{ $comp->code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border-0 rounded-circle shadow-none p-1" style="width: 2rem; height: 2rem;" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2">
                                        <li>
                                            <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openEditCompanyModal({{ json_encode($comp) }})">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Sửa thông tin
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('company.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa doanh nghiệp [{{ $comp->name }}] không?');">
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
                                        {{ $comp->tax_code ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 2. Người đại diện -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-person-badge text-primary me-1"></i> Người đại diện:
                                    </span>
                                    <span class="fw-semibold text-dark">
                                        {{ $comp->representative ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 3. Chức vụ -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-award text-primary me-1"></i> Chức vụ:
                                    </span>
                                    <span class="badge badge-soft-info py-1 px-2 rounded-pill">
                                        {{ $comp->position ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 4. Số điện thoại -->
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="text-muted small fw-medium">
                                        <i class="bi bi-telephone text-primary me-1"></i> Số điện thoại:
                                    </span>
                                    <span class="fw-bold text-primary font-monospace">
                                        {{ $comp->phone ?? 'Chưa cập nhật' }}
                                    </span>
                                </div>

                                <!-- 5. Địa chỉ -->
                                <div class="pt-1">
                                    <span class="text-muted small fw-medium d-block mb-1">
                                        <i class="bi bi-geo-alt text-primary me-1"></i> Địa chỉ trụ sở:
                                    </span>
                                    <div class="text-dark small fw-medium ps-3 border-start border-2 border-primary">
                                        {{ $comp->address ?? 'Chưa cập nhật địa chỉ' }}
                                    </div>
                                </div>
                            </div>

                            @if($comp->notes)
                                <div class="small text-muted fst-italic mb-3 ps-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i> "{{ $comp->notes }}"
                                </div>
                            @endif
                        </div>

                        <!-- Các nút liên kết hóa đơn đầu vào & hóa đơn xuất ra -->
                        <div class="d-flex gap-2 pt-2 border-top">
                            <a href="{{ route('invoice.in', ['company_id' => $comp->id]) }}" class="btn btn-soft-primary btn-sm rounded-pill flex-grow-1 text-center py-2" title="Xem hóa đơn đầu vào của doanh nghiệp này">
                                <i class="bi bi-box-arrow-in-down-left me-1"></i> HĐ Đầu Vào
                            </a>
                            <a href="{{ route('invoice.out', ['company_id' => $comp->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 text-center py-2" title="Xem hóa đơn xuất ra của doanh nghiệp này">
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
                <p class="text-muted small">Hãy thêm thông tin doanh nghiệp bạn đang phụ trách quản lý.</p>
                <button type="button" class="btn btn-theme-primary rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalAddCompany">
                    + Thêm Doanh Nghiệp Đầu Tiên
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL THÊM DOANH NGHIỆP MỚI -->
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
                            Tên doanh nghiệp / Công ty <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Công ty TNHH Cát Vượng..." required>
                    </div>

                    <!-- MST & Mã viết tắt -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã số thuế (MST)</label>
                            <input type="text" name="tax_code" class="form-control rounded-3 shadow-none font-monospace" placeholder="VD: 0109887766">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã viết tắt</label>
                            <input type="text" name="code" class="form-control rounded-3 shadow-none" placeholder="VD: CV-CORP">
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
                            <input type="text" name="position" class="form-control rounded-3 shadow-none" placeholder="VD: Giám Đốc, TGĐ...">
                        </div>
                    </div>

                    <!-- Số điện thoại -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control rounded-3 shadow-none" placeholder="Số hotline hoặc di động...">
                    </div>

                    <!-- Địa chỉ -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Địa chỉ trụ sở</label>
                        <input type="text" name="address" class="form-control rounded-3 shadow-none" placeholder="Địa chỉ đăng ký kinh doanh...">
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Ghi chú thêm</label>
                        <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ghi chú về tài khoản ngân hàng, thông tin nội bộ..."></textarea>
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
                        <label class="form-label fw-semibold text-dark small mb-1">Tên doanh nghiệp / Công ty <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_company_name" class="form-control rounded-3 shadow-none" required>
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

                    <!-- Số điện thoại -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" id="edit_company_phone" class="form-control rounded-3 shadow-none">
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

@push('scripts')
<script>
    function openEditCompanyModal(comp) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditCompany'));
        const form = document.getElementById('editCompanyForm');
        form.action = `/companies/${comp.id}`;

        document.getElementById('edit_company_name').value = comp.name || '';
        document.getElementById('edit_company_tax_code').value = comp.tax_code || '';
        document.getElementById('edit_company_code').value = comp.code || '';
        document.getElementById('edit_company_representative').value = comp.representative || '';
        document.getElementById('edit_company_position').value = comp.position || '';
        document.getElementById('edit_company_phone').value = comp.phone || '';
        document.getElementById('edit_company_address').value = comp.address || '';
        document.getElementById('edit_company_notes').value = comp.notes || '';

        modal.show();
    }
</script>
@endpush
@endsection
