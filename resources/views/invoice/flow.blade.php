@extends('layouts.app')

@php
    $isOut = $flow === 'out';
    $pageTitle = $isOut ? 'Quản Lý Hóa Đơn Xuất Ra (Bán Hàng)' : 'Quản Lý Hóa Đơn Đầu Vào (Mua Hàng / Chi Phí)';
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Thanh Lọc Đa Doanh Nghiệp & Tìm Kiếm Nhanh -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ $isOut ? route('invoice.out') : route('invoice.in') }}" method="GET" class="row g-2 align-items-end">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Chọn CTY (Chỉ hiển thị cho Quản trị viên) -->
                    <div class="col-12 col-md-5 col-lg-4">
                        <label class="form-label text-muted small mb-1 fw-semibold">
                            <i class="bi bi-building me-1 text-primary"></i> CTY:
                        </label>
                        <select name="company_id" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                            <option value="all" {{ ($companyId == 'all' || empty($companyId)) ? 'selected' : '' }}>
                                🏢 Tất cả CTY ({{ $companies->count() }})
                            </option>
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                    {{ $comp->name }} {{ $comp->code ? '('.$comp->code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Lọc theo trạng thái thanh toán -->
                <div class="{{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-6 col-md-3 col-lg-3' : 'col-6 col-md-4 col-lg-3' }}">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-funnel me-1 text-primary"></i> Trạng thái thanh toán:
                    </label>
                    <select name="status" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ empty($status) || $status == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="unpaid" {{ $status == 'unpaid' ? 'selected' : '' }}>⏳ Chưa thanh toán</option>
                        <option value="partial" {{ $status == 'partial' ? 'selected' : '' }}>🔄 Thanh toán 1 phần</option>
                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>❌ Đã hủy</option>
                    </select>
                </div>

                <!-- Tìm kiếm -->
                <div class="{{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-12 col-md-4 col-lg-4' : 'col-12 col-md-8 col-lg-9' }}">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-search me-1 text-primary"></i> Tìm kiếm:
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-3 shadow-none" placeholder="Số HĐ, tên đối tác, diễn giải..." value="{{ $search ?? '' }}">
                        <button class="btn btn-soft-primary px-3 {{ (empty($search) && (empty($companyId) || $companyId == 'all') && (empty($status) || $status == 'all')) ? 'rounded-end-pill' : '' }}" type="submit">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                        @if(!empty($search) || (!empty($companyId) && $companyId != 'all') || (!empty($status) && $status != 'all'))
                            <a href="{{ $isOut ? route('invoice.out') : route('invoice.in') }}" class="btn btn-outline-secondary rounded-end-pill px-2 d-flex align-items-center" title="Đặt lại bộ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Nút thêm nhanh thẳng hàng -->
                <div class="col-12 col-md-4 col-lg-1">
                    <button type="button" class="btn btn-theme-primary btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-1 shadow-sm" style="height: 2.125rem;" data-bs-toggle="modal" data-bs-target="#modalCreateInvoiceFlow" title="Tạo hóa đơn mới">
                        <i class="bi bi-plus-lg"></i> <span>Thêm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ CHI TIẾT THEO LUỒNG HÓA ĐƠN -->
    <div class="row g-3 mb-4">
        <!-- 1. Tổng tiền hóa đơn -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">
                        {{ $isOut ? 'Tổng Hóa Đơn Xuất Ra' : 'Tổng Hóa Đơn Đầu Vào' }}
                    </span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: {{ $isOut ? '#e0f2fe' : '#fef3c7' }}; color: {{ $isOut ? '#0284c7' : '#d97706' }};">
                        <i class="bi {{ $isOut ? 'bi-box-arrow-up-right' : 'bi-box-arrow-in-down-left' }}"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold {{ $isOut ? 'text-primary' : 'text-warning' }} mb-1" style="{{ !$isOut ? 'color: #d97706 !important;' : '' }}">
                    {{ number_format($totalAmount, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    {{ $invoices->total() }} hóa đơn {{ $isOut ? 'bán hàng/dịch vụ' : 'mua hàng/chi phí' }}
                </div>
            </div>
        </div>

        <!-- 2. Đã thanh toán -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">
                        {{ $isOut ? 'Đã Thu Tiền' : 'Đã Trả Tiền' }}
                    </span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #ecfdf5; color: #047857;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-success mb-1">
                    {{ number_format($totalPaid, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    {{ $isOut ? 'Khách hàng đã thanh toán xong' : 'Đã chuyển tiền cho nhà cung cấp' }}
                </div>
            </div>
        </div>

        <!-- 3. Còn chưa thanh toán -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">
                        {{ $isOut ? 'Chưa Thu (Khách Còn Nợ)' : 'Chưa Trả (Còn Nợ NCC)' }}
                    </span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #fee2e2; color: #dc2626;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-danger mb-1">
                    {{ number_format($totalUnpaid, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    {{ $unpaidCount }} hóa đơn đang chờ quyết toán
                </div>
            </div>
        </div>

        <!-- 4. Tiền thuế VAT -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">
                        {{ $isOut ? 'Tiền Thuế VAT Đầu Ra' : 'Tiền Thuế VAT Đầu Vào' }}
                    </span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #f1f5f9; color: #475569;">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark mb-1">
                    {{ number_format($totalTax, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    {{ $isOut ? 'Thuế GTGT phải nộp ngân sách' : 'Thuế GTGT được khấu trừ' }}
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH HÓA ĐƠN -->
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border-soft) !important;">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <i class="bi {{ $isOut ? 'bi-box-arrow-up-right text-primary' : 'bi-box-arrow-in-down-left text-warning' }}"></i>
                    <span>Sổ {{ $isOut ? 'Hóa Đơn Xuất Ra (Bán Hàng)' : 'Hóa Đơn Đầu Vào (Mua Hàng)' }}</span>
                </h5>
                <span class="text-muted small">
                    Hiển thị {{ $invoices->count() }} / {{ $invoices->total() }} hóa đơn
                </span>
            </div>

            <!-- Nút chuyển nhanh giữa 2 sổ hóa đơn -->
            <div class="d-flex gap-2">
                <a href="{{ route('invoice.out') }}" class="btn btn-sm rounded-pill px-3 {{ $isOut ? 'btn-soft-primary fw-bold' : 'btn-outline-secondary' }}">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Sổ Xuất Ra
                </a>
                <a href="{{ route('invoice.in') }}" class="btn btn-sm rounded-pill px-3 {{ !$isOut ? 'btn-soft-primary fw-bold' : 'btn-outline-secondary' }}">
                    <i class="bi bi-box-arrow-in-down-left me-1"></i> Sổ Đầu Vào
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 8.5rem;">Số Hóa Đơn</th>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <th style="width: 11.5rem;">CTY</th>
                        @endif
                        <th style="width: 14rem;">Doanh Nghiệp</th>
                        <th class="text-end" style="width: 8.5rem;">Tiền Trước Thuế</th>
                        <th class="text-center" style="width: 6.5rem;">VAT (%)</th>
                        <th class="text-end" style="width: 9rem;">Tổng Thanh Toán</th>
                        <th class="text-end" style="width: 8.5rem;">Đã Thanh Toán</th>
                        <th class="text-end" style="width: 8.5rem;">Còn Lại</th>
                        <th style="width: 7.5rem;">Ngày Lập</th>
                        <th class="text-center" style="width: 8rem;">Trạng Thái</th>
                        <th class="text-end" style="width: 11.5rem;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $remaining = $inv->remaining_amount;
                            $isPaid = $inv->status === 'paid' || $remaining <= 0;
                        @endphp
                        <tr>
                            <!-- Số hóa đơn -->
                            <td>
                                <span class="fw-semibold text-secondary font-monospace" style="font-size: 0.88rem;">
                                    {{ $inv->invoice_number }}
                                </span>
                                @if($inv->debt_id)
                                    <div class="text-primary" style="font-size: 0.7rem;" title="Có liên kết vào sổ theo dõi">
                                        <i class="bi bi-link-45deg"></i> Có liên kết
                                    </div>
                                @endif
                            </td>

                            <!-- CTY (Chỉ hiển thị cho Quản trị viên) -->
                            @if(Auth::check() && Auth::user()->isAdmin())
                                <td>
                                    <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 11rem;" title="{{ $inv->company->name ?? '' }}">
                                        {{ $inv->company->name ?? '---' }}
                                    </span>
                                    @if(!empty($inv->company->code))
                                        <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.68rem;">
                                            {{ $inv->company->code }}
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- Doanh nghiệp -->
                            <td>
                                @if($inv->partner)
                                    <a href="javascript:void(0)" 
                                       class="fw-semibold text-dark text-truncate d-inline-block text-decoration-none company-detail-link" 
                                       style="max-width: 13rem;" 
                                       title="Nhấn để xem thông tin doanh nghiệp"
                                       onclick='showPartnerDetail(@json($inv->partner))'>
                                        {{ $inv->partner->name }}
                                    </a>
                                @else
                                    <span class="text-muted">---</span>
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
                            <td class="text-end fw-bold text-dark">
                                {{ number_format($inv->total_amount, 0, ',', '.') }} đ
                            </td>

                            <!-- Đã thanh toán -->
                            <td class="text-end text-success fw-medium">
                                {{ number_format($inv->paid_amount, 0, ',', '.') }} đ
                            </td>

                            <!-- Còn lại -->
                            <td class="text-end fw-bold {{ $remaining > 0 ? ($isOut ? 'text-primary' : 'text-danger') : 'text-muted' }}">
                                {{ number_format($remaining, 0, ',', '.') }} đ
                            </td>

                            <!-- Ngày lập -->
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

                            <!-- Trạng thái -->
                            <td class="text-center">
                                @if($inv->status === 'paid' || $remaining <= 0)
                                    <form action="{{ route('invoice.toggle-status', $inv->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="badge badge-soft-success py-1 px-2 rounded-pill border-0 shadow-none text-nowrap" title="Bấm để chuyển về Chưa thanh toán">
                                            <i class="bi bi-check2-circle"></i> Đã thanh toán
                                        </button>
                                    </form>
                                @elseif($inv->paid_amount > 0 && $remaining > 0)
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <button type="button" class="badge py-1 px-2 rounded-pill border-0 shadow-none mb-1 text-nowrap" style="background-color: #fef3c7; color: #d97706; border: 0.0625rem solid #fde68a !important;" onclick='openInvoicePaymentModal(@json($inv))' title="Nhấn để tiếp tục thanh toán">
                                            <i class="bi bi-pie-chart-fill"></i> Thanh toán 1 phần
                                        </button>
                                        <span class="fw-bold font-monospace text-danger" style="font-size: 0.73rem;">
                                            Thiếu: {{ number_format($remaining, 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                @elseif($inv->status === 'cancelled')
                                    <span class="badge badge-soft-danger py-1 px-2 rounded-pill text-nowrap">
                                        <i class="bi bi-x-circle"></i> Đã hủy
                                    </span>
                                @else
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <form action="{{ route('invoice.toggle-status', $inv->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="badge badge-soft-warning py-1 px-2 rounded-pill border-0 shadow-none mb-1 text-nowrap" title="Bấm để đánh dấu Đã thanh toán đủ">
                                                <i class="bi bi-hourglass-split"></i> Chưa thanh toán
                                            </button>
                                        </form>
                                        <span class="fw-bold font-monospace text-danger" style="font-size: 0.73rem;">
                                            Thiếu: {{ number_format($remaining, 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Thao tác: Nút Thanh toán dài ra trên 1 dòng -->
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    @if($remaining > 0)
                                        <button type="button" class="btn btn-sm btn-theme-primary px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1 text-nowrap shadow-sm" title="Ghi nhận thanh toán" onclick="openInvoicePaymentModal({{ json_encode($inv) }})">
                                            <i class="bi bi-credit-card-2-front"></i>
                                            <span>Thanh toán</span>
                                        </button>
                                    @endif

                                    <!-- Nút In hóa đơn -->
                                    <a href="{{ route('invoice.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="In / Xem chi tiết hóa đơn">
                                        <i class="bi bi-printer"></i>
                                    </a>

                                    <!-- Dropdown menu -->
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
                            <td colspan="{{ (Auth::check() && Auth::user()->isAdmin()) ? 11 : 10 }}" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-receipt fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="fw-semibold text-dark">Chưa có {{ $isOut ? 'hóa đơn xuất ra' : 'hóa đơn đầu vào' }} nào</h6>
                                <p class="small text-muted mb-3">Bạn có thể tạo mới hóa đơn để theo dõi doanh thu hoặc chi phí của doanh nghiệp.</p>
                                <button type="button" class="btn btn-theme-primary btn-sm rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCreateInvoiceFlow">
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
<!-- MODAL THÊM HÓA ĐƠN MỚI (Tự chọn đúng loại luồng) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCreateInvoiceFlow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="navbar-brand-badge" style="width: 2rem; height: 2rem; font-size: 1rem;">
                        <i class="bi {{ $isOut ? 'bi-box-arrow-up-right' : 'bi-box-arrow-in-down-left' }}"></i>
                    </span>
                    Tạo Mới {{ $isOut ? 'Hóa Đơn Xuất Ra (Bán Hàng)' : 'Hóa Đơn Đầu Vào (Mua Hàng)' }}
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('invoice.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="{{ $flow }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- CTY -->
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1">
                                    Thuộc CTY <span class="text-danger">*</span>
                                </label>
                                <select name="company_id" class="form-select rounded-3 shadow-none" required>
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                            {{ $comp->name }} {{ $comp->code ? '('.$comp->code.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        @endif

                        <!-- Doanh nghiệp / Đối tác -->
                        <div class="col-12 {{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-md-6' : 'col-md-12' }}">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                {{ $isOut ? 'Khách hàng / Doanh nghiệp nhận HĐ' : 'Nhà cung cấp / Doanh nghiệp xuất HĐ' }} <span class="text-danger">*</span>
                            </label>
                            <select name="partner_id" id="flow_partner_select" class="form-select rounded-3 shadow-none">
                                <option value="">-- Chọn đối tác đã có --</option>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} {{ $p->phone ? '('.$p->phone.')' : '' }}</option>
                                @endforeach
                                <option value="NEW">+ Thêm đối tác mới ngay...</option>
                            </select>
                        </div>

                        <!-- Trường nhập nhanh đối tác mới -->
                        <div class="col-12 p-3 bg-light rounded-3 border d-none" id="flowQuickPartnerBox">
                            <div class="fw-semibold text-dark small mb-2">
                                <i class="bi bi-person-plus text-primary"></i> Nhập nhanh thông tin đối tác mới:
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

                        <!-- Số Hóa Đơn & Diễn giải -->
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Hóa Đơn</label>
                            <input type="text" name="invoice_number" class="form-control rounded-3 shadow-none font-monospace" placeholder="VD: {{ $isOut ? 'HD-XUAT-001' : 'HD-VAO-001' }} (Bỏ trống tự sinh)">
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Nội dung diễn giải <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Cung cấp hàng hóa đợt 1, mua linh kiện văn phòng..." required>
                        </div>

                        <!-- Tiền trước thuế & VAT -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Tiền trước thuế (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="1000" name="subtotal" id="flow_subtotal" class="form-control fw-bold rounded-start-3 shadow-none" placeholder="0" required oninput="calculateFlowInvoiceTotal()">
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Thuế suất VAT (%)</label>
                            <select name="tax_rate" id="flow_tax_rate" class="form-select rounded-3 shadow-none" onchange="calculateFlowInvoiceTotal()">
                                <option value="10">10% (Thuế GTGT phổ thông)</option>
                                <option value="8">8% (Thuế GTGT ưu đãi)</option>
                                <option value="0">0% (Không chịu thuế)</option>
                            </select>
                        </div>

                        <!-- Tóm tắt số tiền -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center" style="background-color: var(--blue-soft-50);">
                                <div>
                                    <span class="text-muted small">Tiền thuế VAT:</span>
                                    <span id="flow_preview_tax" class="fw-bold text-dark ms-1">0 đ</span>
                                </div>
                                <div>
                                    <span class="text-muted small">TỔNG THANH TOÁN:</span>
                                    <span id="flow_preview_total" class="fw-bold text-primary fs-5 ms-1">0 đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Đã thanh toán trước -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Đã thanh toán trước (nếu có)</label>
                            <div class="input-group">
                                <input type="number" step="1000" name="paid_amount" id="flow_create_paid" class="form-control rounded-start-3 shadow-none" placeholder="0" value="0" oninput="updateCreatePaidState()">
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                            <div class="form-text small d-flex justify-content-between mt-1">
                                <span>Còn thiếu: <strong id="flow_create_remaining" class="text-danger font-monospace">0 đ</strong></span>
                            </div>
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái thanh toán</label>
                            <select name="status" id="flow_status_select" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="partial">🔄 Thanh toán 1 phần</option>
                                <option value="paid">✅ Đã thanh toán đủ</option>
                            </select>
                        </div>

                        <!-- Ngày lập & Ngày hẹn trả -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập hóa đơn <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control rounded-3 shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" class="form-control rounded-3 shadow-none">
                        </div>

                        <!-- Tùy chọn liên kết công nợ -->
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sync_debt" id="flow_sync_debt" value="1" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="flow_sync_debt">
                                    Đồng thời đưa vào sổ theo dõi công nợ
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú</label>
                            <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ghi chú về số hợp đồng, tài khoản ngân hàng..."></textarea>
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
<!-- MODAL GHI NHẬN THANH TOÁN CHO HÓA ĐƠN -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalInvoicePayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="navbar-brand-badge" style="width: 2rem; height: 2rem; font-size: 1rem; background-color: #ecfdf5; color: #047857;">
                        <i class="bi bi-credit-card-2-front"></i>
                    </span>
                    <span>Thanh Toán Hóa Đơn</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="invoicePaymentForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded-3" style="background-color: var(--blue-soft-50); border: 0.0625rem solid var(--blue-soft-200);">
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span id="invPayNumber" class="fw-bold font-monospace text-dark"></span>
                            <span id="invPayPartner" class="fw-semibold text-primary"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted small">Tổng tiền hóa đơn:</span>
                            <span id="invPayTotal" class="fw-bold text-dark fs-6">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted small">Đã thanh toán trước đó:</span>
                            <span id="invPayCurrentPaid" class="fw-medium text-success">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline border-top pt-2 mt-2">
                            <span class="text-muted small fw-bold">Hiện còn thiếu:</span>
                            <span id="invPayRemaining" class="fs-5 fw-bold text-danger font-monospace">0 đ</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Số tiền thanh toán đợt này (VNĐ) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="1000" name="amount" id="invPayInputAmount" class="form-control fs-5 fw-bold text-success rounded-start-3 shadow-none" required oninput="previewPaymentRemaining()">
                            <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                        </div>
                        <div id="invPayPreviewResult" class="mt-2 p-2 rounded-3 small bg-light border">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Còn thiếu sau lần trả này:</span>
                                <span id="invPayPreviewRemaining" class="fw-bold text-danger font-monospace">0 đ</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted">Trạng thái mới:</span>
                                <span id="invPayPreviewStatus" class="badge badge-soft-success py-1 px-2 rounded-pill">Đã thanh toán đủ</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Ghi chú thanh toán</label>
                        <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ví dụ: Chuyển khoản đợt 1, tiền mặt..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Thanh Toán
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
                    <span>Chỉnh Sửa Hóa Đơn <span id="edit_inv_display_number" class="font-monospace text-primary"></span></span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editInvoiceForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" id="edit_inv_type" value="{{ $flow }}">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1">Thuộc CTY</label>
                                <select name="company_id" id="edit_inv_company_id" class="form-select rounded-3 shadow-none" required>
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="company_id" id="edit_inv_company_id" value="{{ Auth::user()->company_id }}">
                        @endif
                        <div class="col-12 {{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-md-6' : 'col-md-12' }}">
                            <label class="form-label fw-semibold text-dark small mb-1">Doanh Nghiệp / Đối Tác</label>
                            <select name="partner_id" id="edit_inv_partner_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Số Hóa Đơn</label>
                            <input type="text" name="invoice_number" id="edit_inv_number" class="form-control rounded-3 shadow-none font-monospace" required>
                        </div>
                        <div class="col-12 col-md-7">
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
                            <label class="form-label fw-semibold text-dark small mb-1">Đã thanh toán (VNĐ)</label>
                            <input type="number" step="1000" name="paid_amount" id="edit_inv_paid_amount" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái</label>
                            <select name="status" id="edit_inv_status" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="partial">🔄 Thanh toán 1 phần</option>
                                <option value="paid">✅ Đã thanh toán đủ</option>
                                <option value="cancelled">❌ Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập</label>
                            <input type="date" name="issue_date" id="edit_inv_issue_date" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn trả</label>
                            <input type="date" name="due_date" id="edit_inv_due_date" class="form-control rounded-3 shadow-none">
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

<!-- ========================================================================= -->
<!-- MODAL XEM CHI TIẾT DOANH NGHIỆP -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPartnerDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="navbar-brand-badge" style="width: 2.35rem; height: 2.35rem; font-size: 1.15rem;">
                        <i class="bi bi-building"></i>
                    </span>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="detailPartnerName">Thông Tin Doanh Nghiệp</h6>
                        <span class="badge bg-light text-secondary border rounded-pill mt-1" id="detailPartnerTypeBadge" style="font-size: 0.7rem;"></span>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 rounded-4 mb-3" style="background-color: #f8fafc; border: 0.0625rem solid #e2e8f0;">
                    <!-- Mã số thuế -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-file-earmark-medical text-primary me-1"></i> Mã số thuế (MST):
                        </span>
                        <span class="fw-bold text-dark font-monospace fs-6" id="detailPartnerTaxCode">---</span>
                    </div>

                    <!-- Mã doanh nghiệp -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-tag text-primary me-1"></i> Mã doanh nghiệp:
                        </span>
                        <span class="badge bg-light text-secondary border rounded-pill" id="detailPartnerCode">---</span>
                    </div>

                    <!-- Người đại diện -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-person-badge text-primary me-1"></i> Người đại diện:
                        </span>
                        <span class="fw-semibold text-dark" id="detailPartnerRepresentative">---</span>
                    </div>

                    <!-- Chức vụ -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-award text-primary me-1"></i> Chức vụ:
                        </span>
                        <span class="badge badge-soft-info py-1 px-2 rounded-pill" id="detailPartnerPosition">---</span>
                    </div>

                    <!-- Số điện thoại -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-telephone text-primary me-1"></i> Số điện thoại:
                        </span>
                        <span class="fw-bold text-primary font-monospace" id="detailPartnerPhone">---</span>
                    </div>

                    <!-- Email -->
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">
                            <i class="bi bi-envelope text-primary me-1"></i> Email:
                        </span>
                        <span class="text-dark small" id="detailPartnerEmail">---</span>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="pt-1">
                        <span class="text-muted small fw-medium d-block mb-1">
                            <i class="bi bi-geo-alt text-primary me-1"></i> Địa chỉ trụ sở:
                        </span>
                        <div class="text-dark small fw-medium ps-3 border-start border-2 border-primary" id="detailPartnerAddress">
                            ---
                        </div>
                    </div>
                </div>

                <!-- Ghi chú nếu có -->
                <div class="small text-muted fst-italic ps-1 d-none" id="detailPartnerNotesBox">
                    <i class="bi bi-info-circle me-1"></i> <span id="detailPartnerNotes"></span>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4 d-flex justify-content-between">
                <a href="#" id="detailPartnerFilterLink" class="btn btn-soft-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i> Lọc hóa đơn của DN này
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showPartnerDetail(partner) {
        if (!partner) return;
        const modal = new bootstrap.Modal(document.getElementById('modalPartnerDetail'));

        document.getElementById('detailPartnerName').textContent = partner.name || 'Thông Tin Doanh Nghiệp';

        const typeBadge = document.getElementById('detailPartnerTypeBadge');
        if (partner.type === 'supplier') {
            typeBadge.textContent = '📥 Nhà Cung Cấp';
            typeBadge.className = 'badge badge-soft-warning py-1 px-2 rounded-pill mt-1';
        } else if (partner.type === 'customer') {
            typeBadge.textContent = '📤 Khách Hàng';
            typeBadge.className = 'badge badge-soft-info py-1 px-2 rounded-pill mt-1';
        } else {
            typeBadge.textContent = '🔄 Đối Tác 2 Chiều';
            typeBadge.className = 'badge badge-soft-success py-1 px-2 rounded-pill mt-1';
        }

        document.getElementById('detailPartnerTaxCode').textContent = partner.tax_code || 'Chưa cập nhật';
        document.getElementById('detailPartnerCode').textContent = partner.code || '---';
        document.getElementById('detailPartnerRepresentative').textContent = partner.representative || 'Chưa cập nhật';
        document.getElementById('detailPartnerPosition').textContent = partner.position || 'Chưa cập nhật';
        document.getElementById('detailPartnerPhone').textContent = partner.phone || 'Chưa cập nhật';
        document.getElementById('detailPartnerEmail').textContent = partner.email || 'Chưa cập nhật';
        document.getElementById('detailPartnerAddress').textContent = partner.address || 'Chưa cập nhật địa chỉ';

        const notesBox = document.getElementById('detailPartnerNotesBox');
        const notesEl = document.getElementById('detailPartnerNotes');
        if (partner.notes) {
            notesEl.textContent = partner.notes;
            notesBox.classList.remove('d-none');
        } else {
            notesBox.classList.add('d-none');
        }

        const filterLink = document.getElementById('detailPartnerFilterLink');
        if (filterLink) {
            filterLink.href = `?partner_id=${partner.id}`;
        }

        modal.show();
    }
    function calculateFlowInvoiceTotal() {
        const subtotal = parseFloat(document.getElementById('flow_subtotal').value) || 0;
        const rate = parseFloat(document.getElementById('flow_tax_rate').value) || 0;
        const tax = Math.round(subtotal * (rate / 100));
        const total = subtotal + tax;

        document.getElementById('flow_preview_tax').textContent = new Intl.NumberFormat('vi-VN').format(tax) + ' đ';
        document.getElementById('flow_preview_total').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
        updateCreatePaidState();
    }

    function updateCreatePaidState() {
        const subtotal = parseFloat(document.getElementById('flow_subtotal').value) || 0;
        const rate = parseFloat(document.getElementById('flow_tax_rate').value) || 0;
        const total = subtotal + Math.round(subtotal * (rate / 100));
        const paidInput = document.getElementById('flow_create_paid');
        const paid = parseFloat(paidInput ? paidInput.value : 0) || 0;
        const statusSelect = document.getElementById('flow_status_select');
        const remainingEl = document.getElementById('flow_create_remaining');

        const remaining = Math.max(0, total - paid);
        if (remainingEl) {
            remainingEl.textContent = new Intl.NumberFormat('vi-VN').format(remaining) + ' đ';
            remainingEl.className = remaining > 0 ? 'text-danger font-monospace' : 'text-success font-monospace';
        }

        if (statusSelect) {
            if (paid >= total && total > 0) {
                statusSelect.value = 'paid';
            } else if (paid > 0) {
                statusSelect.value = 'partial';
            } else {
                statusSelect.value = 'unpaid';
            }
        }
    }

    const flowPartnerSelect = document.getElementById('flow_partner_select');
    const flowQuickBox = document.getElementById('flowQuickPartnerBox');
    if (flowPartnerSelect && flowQuickBox) {
        flowPartnerSelect.addEventListener('change', function() {
            if (this.value === 'NEW') {
                flowQuickBox.classList.remove('d-none');
                flowQuickBox.querySelector('input[name="new_partner_name"]').focus();
            } else {
                flowQuickBox.classList.add('d-none');
            }
        });
    }

    let currentInvoiceRemaining = 0;
    let currentInvoiceTotal = 0;
    let currentInvoicePaid = 0;

    function openInvoicePaymentModal(inv) {
        const modal = new bootstrap.Modal(document.getElementById('modalInvoicePayment'));
        const form = document.getElementById('invoicePaymentForm');
        form.action = `/invoices/${inv.id}/pay`;

        currentInvoiceTotal = parseFloat(inv.total_amount) || 0;
        currentInvoicePaid = parseFloat(inv.paid_amount) || 0;
        currentInvoiceRemaining = Math.max(0, currentInvoiceTotal - currentInvoicePaid);

        document.getElementById('invPayNumber').textContent = inv.invoice_number;
        document.getElementById('invPayPartner').textContent = inv.partner ? inv.partner.name : '';
        document.getElementById('invPayTotal').textContent = new Intl.NumberFormat('vi-VN').format(currentInvoiceTotal) + ' đ';
        document.getElementById('invPayCurrentPaid').textContent = new Intl.NumberFormat('vi-VN').format(currentInvoicePaid) + ' đ';
        document.getElementById('invPayRemaining').textContent = new Intl.NumberFormat('vi-VN').format(currentInvoiceRemaining) + ' đ';

        document.getElementById('invPayInputAmount').value = currentInvoiceRemaining;
        previewPaymentRemaining();

        modal.show();
    }

    function previewPaymentRemaining() {
        const amount = parseFloat(document.getElementById('invPayInputAmount').value) || 0;
        const newRemaining = Math.max(0, currentInvoiceRemaining - amount);
        const previewRemainingEl = document.getElementById('invPayPreviewRemaining');
        const previewStatusEl = document.getElementById('invPayPreviewStatus');

        previewRemainingEl.textContent = new Intl.NumberFormat('vi-VN').format(newRemaining) + ' đ';
        if (newRemaining <= 0 && amount > 0) {
            previewRemainingEl.className = 'fw-bold text-success font-monospace';
            previewStatusEl.textContent = '✅ Đã thanh toán đủ 100%';
            previewStatusEl.className = 'badge badge-soft-success py-1 px-2 rounded-pill';
        } else if (amount > 0) {
            previewRemainingEl.className = 'fw-bold text-danger font-monospace';
            previewStatusEl.textContent = '🔄 Thanh toán 1 phần (Còn thiếu)';
            previewStatusEl.className = 'badge badge-soft-warning py-1 px-2 rounded-pill';
        } else {
            previewRemainingEl.className = 'fw-bold text-danger font-monospace';
            previewStatusEl.textContent = '⏳ Chưa thanh toán đợt này';
            previewStatusEl.className = 'badge badge-soft-warning py-1 px-2 rounded-pill';
        }
    }

    function openEditInvoiceModal(inv) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditInvoice'));
        const form = document.getElementById('editInvoiceForm');
        form.action = `/invoices/${inv.id}`;

        document.getElementById('edit_inv_display_number').textContent = `[${inv.invoice_number}]`;
        document.getElementById('edit_inv_company_id').value = inv.company_id;
        document.getElementById('edit_inv_partner_id').value = inv.partner_id;
        document.getElementById('edit_inv_number').value = inv.invoice_number;
        document.getElementById('edit_inv_title').value = inv.title;
        document.getElementById('edit_inv_subtotal').value = inv.subtotal;
        document.getElementById('edit_inv_tax_rate').value = parseInt(inv.tax_rate);
        document.getElementById('edit_inv_paid_amount').value = inv.paid_amount || 0;
        document.getElementById('edit_inv_issue_date').value = inv.issue_date ? inv.issue_date.substring(0, 10) : '';
        document.getElementById('edit_inv_due_date').value = inv.due_date ? inv.due_date.substring(0, 10) : '';
        document.getElementById('edit_inv_status').value = inv.status;
        document.getElementById('edit_inv_notes').value = inv.notes || '';

        modal.show();
    }
</script>
@endpush
@endsection
