@extends('layouts.app')

@section('title', 'Quản Lý Hóa Đơn Cho Từng Công Ty')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Thanh Lọc Hóa Đơn (Bo góc rounded-4 mượt mà, căn chỉnh chuẩn xác) -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('invoice.index') }}" method="GET" class="row g-2 align-items-end">
                <!-- Chọn công ty / đơn vị -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-building me-1 text-primary"></i> Đơn vị / Công ty:
                    </label>
                    <select name="company_id" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ ($companyId == 'all' || empty($companyId)) ? 'selected' : '' }}>
                            🏢 Tất cả đơn vị ({{ $companies->count() }})
                        </option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} {{ $comp->code ? '('.$comp->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lọc theo loại hóa đơn -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-arrow-left-right me-1 text-primary"></i> Loại hóa đơn:
                    </label>
                    <select name="type" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ empty($type) || $type == 'all' ? 'selected' : '' }}>Tất cả các loại</option>
                        <option value="out" {{ $type == 'out' ? 'selected' : '' }}>📤 Hóa Đơn Đầu Ra (Bán Hàng)</option>
                        <option value="in" {{ $type == 'in' ? 'selected' : '' }}>📥 Hóa Đơn Đầu Vào (Mua Hàng)</option>
                    </select>
                </div>

                <!-- Lọc theo trạng thái -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-funnel me-1 text-primary"></i> Trạng thái:
                    </label>
                    <select name="status" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ empty($status) || $status == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="unpaid" {{ $status == 'unpaid' ? 'selected' : '' }}>⏳ Chưa thanh toán</option>
                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                    </select>
                </div>

                <!-- Tìm kiếm -->
                <div class="col-12 col-md-8 col-lg-4">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-search me-1 text-primary"></i> Tìm kiếm:
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-3 shadow-none" placeholder="Số hóa đơn, tên đối tác, diễn giải..." value="{{ $search ?? '' }}">
                        <button class="btn btn-soft-primary px-3 {{ (empty($search) && (empty($companyId) || $companyId == 'all') && (empty($type) || $type == 'all') && (empty($status) || $status == 'all')) ? 'rounded-end-pill' : '' }}" type="submit">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                        @if(!empty($search) || (!empty($companyId) && $companyId != 'all') || (!empty($type) && $type != 'all') || (!empty($status) && $status != 'all'))
                            <a href="{{ route('invoice.index') }}" class="btn btn-outline-secondary rounded-end-pill px-2 d-flex align-items-center" title="Đặt lại bộ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Nút thêm nhanh -->
                <div class="col-12 col-md-4 col-lg-1">
                    <button type="button" class="btn btn-theme-primary btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-1 shadow-sm" style="height: 2.125rem;" data-bs-toggle="modal" data-bs-target="#modalCreateInvoice" title="Tạo hóa đơn mới">
                        <i class="bi bi-plus-lg"></i> <span>Thêm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ HÓA ĐƠN -->
    <div class="row g-3 mb-4">
        <!-- 1. Hóa đơn đầu ra -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Hóa Đơn Đầu Ra (Bán Hàng)</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-primary mb-1">
                    {{ number_format($totalOut, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    Tiền thuế VAT đầu ra: <span class="fw-semibold text-dark">{{ number_format($taxOut, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- 2. Hóa đơn đầu vào -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Hóa Đơn Đầu Vào (Mua Hàng)</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #fef3c7; color: #d97706;">
                        <i class="bi bi-box-arrow-in-down-left"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-warning mb-1" style="color: #d97706 !important;">
                    {{ number_format($totalIn, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    Tiền thuế VAT đầu vào: <span class="fw-semibold text-dark">{{ number_format($taxIn, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- 3. Thuế VAT chênh lệch -->
        @php
            $vatDifference = $taxOut - $taxIn;
        @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Thuế VAT Chênh Lệch</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: {{ $vatDifference >= 0 ? '#ecfdf5' : '#f0fdf4' }}; color: #047857;">
                        <i class="bi bi-calculator"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold mb-1 {{ $vatDifference >= 0 ? 'text-success' : 'text-primary' }}">
                    {{ number_format(abs($vatDifference), 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    @if($vatDifference >= 0)
                        <i class="bi bi-info-circle text-success me-1"></i> Thuế VAT ước tính cần nộp
                    @else
                        <i class="bi bi-check-circle text-primary me-1"></i> Thuế VAT được khấu trừ chuyển kỳ sau
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. Hóa đơn chưa thanh toán -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Chưa Thanh Toán</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #fee2e2; color: #dc2626;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-danger mb-1">
                    {{ $unpaidCount }} <span class="fs-6 fw-normal text-muted">hóa đơn</span>
                </div>
                <div class="d-flex justify-content-between align-items-center small border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    <span class="text-muted">Theo dõi thanh toán hóa đơn</span>
                    @if($unpaidCount > 0)
                        <a href="{{ route('invoice.index', ['status' => 'unpaid']) }}" class="text-danger fw-semibold text-decoration-none">
                            Xem ngay <i class="bi bi-arrow-right"></i>
                        </a>
                    @else
                        <span class="text-success fw-medium">Đã tất toán</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH HÓA ĐƠN -->
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border-soft) !important;">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt me-1 text-primary"></i> Sổ Hóa Đơn Cho Từng Doanh Nghiệp
                </h5>
                <span class="text-muted small">
                    Hiển thị {{ $invoices->count() }} / {{ $invoices->total() }} hóa đơn
                </span>
            </div>

            <!-- Tab phân loại đầu ra / đầu vào -->
            <div class="btn-group btn-group-sm p-1 rounded-pill" style="background-color: #f1f5f9;" role="group">
                <a href="{{ route('invoice.index', array_merge(request()->query(), ['type' => 'all'])) }}" class="btn rounded-pill px-3 {{ empty($type) || $type == 'all' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    Tất cả ({{ $invoices->total() }})
                </a>
                <a href="{{ route('invoice.index', array_merge(request()->query(), ['type' => 'out'])) }}" class="btn rounded-pill px-3 {{ $type == 'out' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    📤 Đầu Ra (Bán Hàng)
                </a>
                <a href="{{ route('invoice.index', array_merge(request()->query(), ['type' => 'in'])) }}" class="btn rounded-pill px-3 {{ $type == 'in' ? 'btn-white bg-white text-warning shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    📥 Đầu Vào (Mua Hàng)
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 8rem;">Số Hóa Đơn</th>
                        <th style="width: 11rem;">Đơn Vị Xuất/Nhận</th>
                        <th style="width: 14rem;">Đối Tác / Khách Hàng</th>
                        <th style="width: 8rem;">Phân Loại</th>
                        <th class="text-end" style="width: 8.5rem;">Tiền Trước Thuế</th>
                        <th class="text-center" style="width: 6rem;">Thuế VAT</th>
                        <th class="text-end" style="width: 9rem;">Tổng Thanh Toán</th>
                        <th style="width: 8rem;">Ngày Lập</th>
                        <th class="text-center" style="width: 8.5rem;">Trạng Thái</th>
                        <th class="text-end" style="width: 10rem;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $isOut = $inv->type === 'out';
                        @endphp
                        <tr>
                            <!-- Số hóa đơn -->
                            <td>
                                <span class="fw-semibold text-secondary font-monospace" style="font-size: 0.88rem;">
                                    {{ $inv->invoice_number }}
                                </span>
                                @if($inv->debt_id)
                                    <div class="text-primary" style="font-size: 0.7rem;" title="Có liên kết công nợ">
                                        <i class="bi bi-link-45deg"></i> Có liên kết nợ
                                    </div>
                                @endif
                            </td>

                            <!-- Đơn vị quản lý -->
                            <td>
                                <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 10.5rem;" title="{{ $inv->company->name ?? '' }}">
                                    {{ $inv->company->name ?? '---' }}
                                </span>
                                @if(!empty($inv->company->code))
                                    <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.68rem;">
                                        {{ $inv->company->code }}
                                    </span>
                                @endif
                            </td>

                            <!-- Đối tác & Diễn giải -->
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 13rem;" title="{{ $inv->partner->name ?? '' }}">
                                    {{ $inv->partner->name ?? '---' }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 13rem;" title="{{ $inv->title }}">
                                    {{ $inv->title }}
                                </div>
                            </td>

                            <!-- Phân loại hóa đơn -->
                            <td>
                                @if($isOut)
                                    <span class="badge badge-soft-info d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill">
                                        <i class="bi bi-box-arrow-up-right"></i> Đầu Ra
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill">
                                        <i class="bi bi-box-arrow-in-down-left"></i> Đầu Vào
                                    </span>
                                @endif
                            </td>

                            <!-- Tiền trước thuế -->
                            <td class="text-end fw-medium text-secondary">
                                {{ number_format($inv->subtotal, 0, ',', '.') }} đ
                            </td>

                            <!-- Thuế suất VAT -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.72rem;">
                                    {{ (float)$inv->tax_rate }}%
                                </span>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    +{{ number_format($inv->tax_amount, 0, ',', '.') }} đ
                                </div>
                            </td>

                            <!-- Tổng thanh toán -->
                            <td class="text-end fw-bold {{ $isOut ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($inv->total_amount, 0, ',', '.') }} đ
                            </td>

                            <!-- Ngày lập hóa đơn -->
                            <td>
                                <div class="text-dark small">
                                    {{ \Carbon\Carbon::parse($inv->issue_date)->format('d/m/Y') }}
                                </div>
                                @if($inv->due_date)
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        Hạn: {{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Trạng thái & 1-click toggle -->
                            <td class="text-center">
                                <form action="{{ route('invoice.toggle-status', $inv->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($inv->status === 'paid')
                                        <button type="submit" class="badge badge-soft-success py-1 px-2 rounded-pill border-0 shadow-none" title="Bấm để đổi thành Chưa thanh toán">
                                            <i class="bi bi-check2-circle"></i> Đã thanh toán
                                        </button>
                                    @elseif($inv->status === 'unpaid')
                                        <button type="submit" class="badge badge-soft-warning py-1 px-2 rounded-pill border-0 shadow-none" title="Bấm để đánh dấu Đã thanh toán">
                                            <i class="bi bi-hourglass-split"></i> Chưa thanh toán
                                        </button>
                                    @else
                                        <span class="badge bg-secondary py-1 px-2 rounded-pill">
                                            Đã hủy
                                        </span>
                                    @endif
                                </form>
                            </td>

                            <!-- Thao tác -->
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    <!-- Nút xem / in hóa đơn -->
                                    <a href="{{ route('invoice.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-soft-primary rounded-pill px-2 py-1" title="In / Xem hóa đơn chi tiết">
                                        <i class="bi bi-printer"></i> In HĐ
                                    </a>

                                    <!-- Dropdown sửa / xóa -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle p-1 shadow-none" style="width: 2rem; height: 2rem;" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2">
                                            <li>
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openEditInvoiceModal({{ json_encode($inv) }})">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Sửa hóa đơn
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('invoice.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hóa đơn [{{ $inv->invoice_number }}] này không?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item small text-danger py-2 rounded-3">
                                                        <i class="bi bi-trash3 me-2"></i> Xóa hóa đơn
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-receipt fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="fw-semibold text-dark">Chưa có hóa đơn nào phù hợp</h6>
                                <p class="small text-muted mb-3">Bạn có thể tạo hóa đơn mới để quản lý chứng từ đầu vào và đầu ra.</p>
                                <button type="button" class="btn btn-theme-primary btn-sm rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCreateInvoice">
                                    <i class="bi bi-plus-lg me-1"></i> Tạo Hóa Đơn Mới
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top" style="border-color: var(--border-soft) !important;">
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL THÊM HÓA ĐƠN MỚI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCreateInvoice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="navbar-brand-badge" style="width: 2rem; height: 2rem; font-size: 1rem;">
                        <i class="bi bi-receipt"></i>
                    </span>
                    Tạo Hóa Đơn Mới Cho Doanh Nghiệp
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('invoice.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Loại hóa đơn -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Loại hóa đơn <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="inv_type_out" value="out" checked>
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center rounded-3" for="inv_type_out">
                                        <i class="bi bi-box-arrow-up-right fs-5 mb-1"></i>
                                        <span class="fw-bold">Hóa Đơn Đầu Ra (Bán Hàng)</span>
                                        <span class="small opacity-75" style="font-size: 0.75rem;">Xuất cho khách hàng khi bán hàng, làm dịch vụ</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="inv_type_in" value="in">
                                    <label class="btn btn-outline-warning w-100 py-2 d-flex flex-column align-items-center rounded-3" for="inv_type_in">
                                        <i class="bi bi-box-arrow-in-down-left fs-5 mb-1"></i>
                                        <span class="fw-bold text-dark">Hóa Đơn Đầu Vào (Mua Hàng)</span>
                                        <span class="small text-muted" style="font-size: 0.75rem;">Nhận từ nhà cung cấp vật tư, chi phí văn phòng</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Doanh nghiệp / Công ty quản lý -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Thuộc Doanh nghiệp / Công ty <span class="text-danger">*</span>
                            </label>
                            <select name="company_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }} {{ $comp->code ? '('.$comp->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Đối tác / Khách hàng -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Đối tác / Khách hàng / NCC <span class="text-danger">*</span>
                            </label>
                            <select name="partner_id" id="inv_partner_select" class="form-select rounded-3 shadow-none">
                                <option value="">-- Chọn đối tác đã có --</option>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->phone ? '('.$p->phone.')' : '' }}</option>
                                @endforeach
                                <option value="NEW">+ Thêm đối tác mới ngay...</option>
                            </select>
                        </div>

                        <!-- Trường nhập nhanh đối tác mới (ẩn mặc định) -->
                        <div class="col-12 p-3 bg-light rounded-3 border d-none" id="invQuickPartnerBox">
                            <div class="fw-semibold text-dark small mb-2">
                                <i class="bi bi-person-plus text-primary"></i> Nhập nhanh đối tác mới:
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-7">
                                    <input type="text" name="new_partner_name" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Tên đối tác hoặc khách hàng...">
                                </div>
                                <div class="col-12 col-md-5">
                                    <input type="text" name="new_partner_phone" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Số điện thoại liên hệ...">
                                </div>
                            </div>
                        </div>

                        <!-- Số hóa đơn & Tiêu đề -->
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Hóa Đơn</label>
                            <input type="text" name="invoice_number" class="form-control rounded-3 shadow-none font-monospace" placeholder="VD: HD-2026-001 (Bỏ trống tự sinh)">
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Nội dung diễn giải <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Cung cấp thiết bị điều khiển đợt 1..." required>
                        </div>

                        <!-- Tiền trước thuế & Thuế suất VAT -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Tiền trước thuế (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="1000" name="subtotal" id="inv_subtotal" class="form-control fw-bold rounded-start-3 shadow-none" placeholder="0" required oninput="calculateInvoiceTotal()">
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Thuế suất VAT (%)</label>
                            <select name="tax_rate" id="inv_tax_rate" class="form-select rounded-3 shadow-none" onchange="calculateInvoiceTotal()">
                                <option value="10">10% (Thuế GTGT phổ thông)</option>
                                <option value="8">8% (Thuế GTGT ưu đãi)</option>
                                <option value="0">0% (Không chịu thuế)</option>
                            </select>
                        </div>

                        <!-- Tóm tắt số tiền tự tính -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center" style="background-color: var(--blue-soft-50);">
                                <div>
                                    <span class="text-muted small">Tiền thuế VAT:</span>
                                    <span id="preview_tax_amount" class="fw-bold text-dark ms-1">0 đ</span>
                                </div>
                                <div>
                                    <span class="text-muted small">TỔNG THANH TOÁN:</span>
                                    <span id="preview_total_amount" class="fw-bold text-primary fs-5 ms-1">0 đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ngày lập & Ngày hẹn thanh toán -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập hóa đơn <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control rounded-3 shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" class="form-control rounded-3 shadow-none">
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái thanh toán</label>
                            <select name="status" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="paid">✅ Đã thanh toán</option>
                            </select>
                        </div>

                        <!-- Tùy chọn tự động liên kết công nợ -->
                        <div class="col-12 col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sync_debt" id="sync_debt" value="1" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="sync_debt">
                                    Đồng thời tạo 1 khoản công nợ tương ứng
                                </label>
                                <div class="form-text small" style="font-size: 0.72rem;">Tự động đưa số tiền này vào sổ theo dõi công nợ</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú</label>
                            <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ghi chú về số hợp đồng, tài khoản xuất hóa đơn..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Hóa Đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL SỬA HÓA ĐƠN -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditInvoice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Chỉnh Sửa Hóa Đơn <span id="edit_invoice_number_display" class="font-monospace text-primary"></span></span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInvoiceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Loại hóa đơn</label>
                            <select name="type" id="edit_inv_type" class="form-select rounded-3 shadow-none" required>
                                <option value="out">📤 Hóa Đơn Đầu Ra (Bán Hàng)</option>
                                <option value="in">📥 Hóa Đơn Đầu Vào (Mua Hàng)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Thuộc Doanh nghiệp / Công ty</label>
                            <select name="company_id" id="edit_inv_company_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Đối tác / Khách hàng</label>
                            <select name="partner_id" id="edit_inv_partner_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Hóa Đơn</label>
                            <input type="text" name="invoice_number" id="edit_inv_number" class="form-control rounded-3 shadow-none font-monospace" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Nội dung diễn giải</label>
                            <input type="text" name="title" id="edit_inv_title" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Tiền trước thuế (VNĐ)</label>
                            <input type="number" step="1000" name="subtotal" id="edit_inv_subtotal" class="form-control fw-bold rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Thuế suất VAT (%)</label>
                            <select name="tax_rate" id="edit_inv_tax_rate" class="form-select rounded-3 shadow-none">
                                <option value="10">10%</option>
                                <option value="8">8%</option>
                                <option value="0">0%</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập hóa đơn</label>
                            <input type="date" name="issue_date" id="edit_inv_issue_date" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" id="edit_inv_due_date" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái</label>
                            <select name="status" id="edit_inv_status" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="paid">✅ Đã thanh toán</option>
                                <option value="cancelled">❌ Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú</label>
                            <textarea name="notes" id="edit_inv_notes" class="form-control rounded-3 shadow-none" rows="2"></textarea>
                        </div>
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
    // Tự động tính tiền thuế và tổng tiền hóa đơn
    function calculateInvoiceTotal() {
        const subtotal = parseFloat(document.getElementById('inv_subtotal').value) || 0;
        const rate = parseFloat(document.getElementById('inv_tax_rate').value) || 0;
        const tax = Math.round(subtotal * (rate / 100));
        const total = subtotal + tax;

        document.getElementById('preview_tax_amount').textContent = new Intl.NumberFormat('vi-VN').format(tax) + ' đ';
        document.getElementById('preview_total_amount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
    }

    // Toggle nhập nhanh đối tác
    const invPartnerSelect = document.getElementById('inv_partner_select');
    const invQuickBox = document.getElementById('invQuickPartnerBox');
    if (invPartnerSelect && invQuickBox) {
        invPartnerSelect.addEventListener('change', function() {
            if (this.value === 'NEW') {
                invQuickBox.classList.remove('d-none');
                invQuickBox.querySelector('input[name="new_partner_name"]').focus();
            } else {
                invQuickBox.classList.add('d-none');
            }
        });
    }

    // Mở modal sửa hóa đơn
    function openEditInvoiceModal(inv) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditInvoice'));
        const form = document.getElementById('editInvoiceForm');
        form.action = `/invoices/${inv.id}`;

        document.getElementById('edit_invoice_number_display').textContent = `[${inv.invoice_number}]`;
        document.getElementById('edit_inv_type').value = inv.type;
        document.getElementById('edit_inv_company_id').value = inv.company_id;
        document.getElementById('edit_inv_partner_id').value = inv.partner_id;
        document.getElementById('edit_inv_number').value = inv.invoice_number;
        document.getElementById('edit_inv_title').value = inv.title;
        document.getElementById('edit_inv_subtotal').value = inv.subtotal;
        document.getElementById('edit_inv_tax_rate').value = parseInt(inv.tax_rate);
        document.getElementById('edit_inv_issue_date').value = inv.issue_date ? inv.issue_date.substring(0, 10) : '';
        document.getElementById('edit_inv_due_date').value = inv.due_date ? inv.due_date.substring(0, 10) : '';
        document.getElementById('edit_inv_status').value = inv.status;
        document.getElementById('edit_inv_notes').value = inv.notes || '';

        modal.show();
    }
</script>
@endpush
@endsection
