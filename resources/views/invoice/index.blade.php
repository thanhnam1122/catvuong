@extends('layouts.app')

@section('title', 'Quản Lý Hóa Đơn Cho Từng Công Ty')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Thanh Lọc Hóa Đơn (Bo góc rounded-4 mượt mà, căn chỉnh chuẩn xác) -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('invoice.index') }}" method="GET" class="row g-2 align-items-end">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Chọn công ty / đơn vị (Chỉ dành cho Quản trị viên) -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label text-muted small mb-1 fw-semibold">
                            <i class="bi bi-building me-1 text-primary"></i> Đơn vị / CTY:
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
                @endif

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
                        <option value="partial" {{ $status == 'partial' ? 'selected' : '' }}>🔄 Thanh toán 1 phần</option>
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
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <th style="width: 11rem;">CTY</th>
                        @endif
                        <th style="width: 14rem;">Doanh Nghiệp</th>
                        <th style="width: 7.5rem;">Loại HĐ</th>
                        <th class="text-center" style="width: 8.5rem;">Phân Loại</th>
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
                            $invEditData = [
                                'id' => $inv->id,
                                'invoice_number' => $inv->invoice_number,
                                'company_id' => $inv->company_id,
                                'partner_id' => $inv->partner_id,
                                'type' => $inv->type,
                                'title' => $inv->title,
                                'subtotal' => $inv->subtotal,
                                'tax_rate' => $inv->tax_rate,
                                'tax_amount' => $inv->tax_amount,
                                'paid_amount' => $inv->paid_amount,
                                'paid_date' => $inv->paid_date,
                                'issue_date' => $inv->issue_date,
                                'due_date' => $inv->due_date,
                                'status' => $inv->status,
                                'notes' => $inv->notes,
                                'is_concerned' => $inv->is_concerned,
                            ];
                        @endphp
                        <tr>
                            <!-- Số hóa đơn (Bấm vào để xem & đính kèm file) -->
                            <td>
                                <button type="button" 
                                        class="btn btn-link p-0 fw-bold text-primary font-monospace text-decoration-none d-inline-flex align-items-center gap-1 shadow-none"
                                        style="font-size: 0.9rem;" 
                                        onclick="openInvoiceAttachmentsModal({{ $inv->id }}, '{{ $inv->invoice_number }}')"
                                        title="Nhấn để xem & đính kèm file (HĐ, BG, HS liên quan, Hóa đơn)">
                                    <i class="bi bi-paperclip fs-6"></i>
                                    <span>{{ $inv->invoice_number }}</span>
                                </button>
                                <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                    <button type="button" 
                                            class="badge {{ ($inv->attachments_count ?? $inv->attachments->count()) > 0 ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-light text-muted border' }} border rounded-pill px-2 py-0 text-decoration-none shadow-none" 
                                            style="font-size: 0.68rem; cursor: pointer;"
                                            onclick="openInvoiceAttachmentsModal({{ $inv->id }}, '{{ $inv->invoice_number }}')"
                                            title="Quản lý file đính kèm của hóa đơn này">
                                        @if(($inv->attachments_count ?? $inv->attachments->count()) > 0)
                                            <i class="bi bi-file-earmark-check-fill me-1 text-primary"></i><strong>{{ $inv->attachments_count ?? $inv->attachments->count() }}</strong> file
                                        @else
                                            <i class="bi bi-plus-lg me-1"></i>+ File
                                        @endif
                                    </button>

                                    @if($inv->debt_id)
                                        <span class="text-secondary small d-inline-flex align-items-center" style="font-size: 0.68rem;" title="Có liên kết công nợ">
                                            <i class="bi bi-link-45deg"></i> Nợ
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Đơn vị quản lý (Chỉ hiển thị cho Quản trị viên) -->
                            @if(Auth::check() && Auth::user()->isAdmin())
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

                            <!-- Phân loại hóa đơn (Đầu Ra / Đầu Vào) -->
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

                            <!-- Phân loại theo dõi (Quan tâm / Không quan tâm) -->
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-sm p-0 border-0 bg-transparent shadow-none"
                                        id="btn-concern-{{ $inv->id }}"
                                        onclick="toggleInvoiceConcern({{ $inv->id }})"
                                        title="Nhấn để đổi sang {{ $inv->is_concerned ? 'Không quan tâm' : 'Quan tâm' }}">
                                    @if($inv->is_concerned)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; cursor: pointer;">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <span>Quan tâm</span>
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; cursor: pointer;">
                                            <i class="bi bi-dash-circle text-muted"></i>
                                            <span>Không quan tâm</span>
                                        </span>
                                    @endif
                                </button>
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
                                @php
                                    $remaining = $inv->remaining_amount;
                                @endphp
                                @if($inv->status === 'paid' || $remaining <= 0)
                                    <form action="{{ route('invoice.toggle-status', $inv->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="badge badge-soft-success py-1 px-2 rounded-pill border-0 shadow-none text-nowrap" title="Bấm để đổi thành Chưa thanh toán">
                                            <i class="bi bi-check2-circle"></i> Đã thanh toán
                                        </button>
                                    </form>
                                @elseif($inv->paid_amount > 0 && $remaining > 0)
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <span class="badge py-1 px-2 rounded-pill border-0 shadow-none mb-1 text-nowrap" style="background-color: #fef3c7; color: #d97706; border: 0.0625rem solid #fde68a !important;">
                                            <i class="bi bi-pie-chart-fill"></i> Thanh toán 1 phần
                                        </span>
                                        <span class="fw-bold font-monospace text-danger" style="font-size: 0.73rem;">
                                            Thiếu: {{ number_format($remaining, 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                @elseif($inv->status === 'cancelled')
                                    <span class="badge bg-secondary py-1 px-2 rounded-pill text-nowrap">
                                        <i class="bi bi-x-circle"></i> Đã hủy
                                    </span>
                                @else
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <form action="{{ route('invoice.toggle-status', $inv->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="badge badge-soft-warning py-1 px-2 rounded-pill border-0 shadow-none mb-1 text-nowrap" title="Bấm để đánh dấu Đã thanh toán">
                                                <i class="bi bi-hourglass-split"></i> Chưa thanh toán
                                            </button>
                                        </form>
                                        <span class="fw-bold font-monospace text-danger" style="font-size: 0.73rem;">
                                            Thiếu: {{ number_format($remaining, 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                @endif
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
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openInvoiceAttachmentsModal({{ $inv->id }}, '{{ $inv->invoice_number }}')">
                                                    <i class="bi bi-paperclip me-2 text-primary"></i> Đính kèm file ({{ $inv->attachments_count ?? $inv->attachments->count() }})
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick="toggleInvoiceConcern({{ $inv->id }})">
                                                    <i class="bi bi-star me-2 text-warning"></i> Chuyển sang: {{ $inv->is_concerned ? 'Không quan tâm' : 'Quan tâm' }}
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick='openEditInvoiceModal(@json($invEditData))'>
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

                        <!-- Đối tác / Khách hàng -->
                        <div class="col-12 {{ (Auth::check() && Auth::user()->isAdmin()) ? 'col-md-6' : 'col-md-12' }}">
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
                                <option value="5">5% (Thuế GTGT 5%)</option>
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

                        <!-- Ngày lập & Ngày hẹn thanh toán & Phân loại -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập hóa đơn <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control rounded-3 shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Phân loại theo dõi</label>
                            <select name="is_concerned" class="form-select rounded-3 shadow-none">
                                <option value="1" selected>⭐ Quan tâm (Nguyên liệu...)</option>
                                <option value="0">⚪ Không quan tâm (Tiếp khách...)</option>
                            </select>
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái thanh toán</label>
                            <select name="status" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="partial">🔄 Thanh toán 1 phần</option>
                                <option value="paid">✅ Đã thanh toán đủ</option>
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
                                <option value="5">5%</option>
                                <option value="0">0%</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày lập hóa đơn</label>
                            <input type="date" name="issue_date" id="edit_inv_issue_date" class="form-control rounded-3 shadow-none" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" id="edit_inv_due_date" class="form-control rounded-3 shadow-none">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Phân loại theo dõi</label>
                            <select name="is_concerned" id="edit_inv_is_concerned" class="form-select rounded-3 shadow-none">
                                <option value="1">⭐ Quan tâm</option>
                                <option value="0">⚪ Không quan tâm</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Trạng thái</label>
                            <select name="status" id="edit_inv_status" class="form-select rounded-3 shadow-none" required>
                                <option value="unpaid">⏳ Chưa thanh toán</option>
                                <option value="partial">🔄 Thanh toán 1 phần</option>
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

<!-- Modal Đính Kèm File Cho Hóa Đơn (HĐ, BG, HS liên quan, Hóa đơn) -->
<div class="modal fade" id="modalInvoiceAttachments" tabindex="-1" aria-labelledby="modalInvoiceAttachmentsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background-color: var(--blue-soft-50);">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 2.2rem; height: 2.2rem; font-size: 1rem;">
                        <i class="bi bi-paperclip"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="modalInvoiceAttachmentsLabel">
                            File Đính Kèm Hóa Đơn <span id="attModalInvoiceNumber" class="text-primary font-monospace"></span>
                        </h5>
                        <div class="text-muted" style="font-size: 0.75rem;" id="attModalInvoiceSub">
                            Đính kèm HĐ, BG, HS liên quan, hóa đơn chứng từ, ủy nhiệm chi (UNC)
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
                        <form id="uploadAttachmentForm" method="POST" enctype="multipart/form-data" onsubmit="handleUploadAttachments(event)">
                            @csrf
                            <input type="hidden" id="att_invoice_id" name="invoice_id" value="">
                            
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-dark mb-1">
                                        Phân loại tài liệu <span class="text-danger">*</span>
                                    </label>
                                    <select name="category" id="att_category" class="form-select form-select-sm rounded-3 shadow-none" required>
                                        <option value="invoice">📄 Hóa đơn (Hóa đơn GTGT / Chứng từ gốc)</option>
                                        <option value="contract">📑 HĐ (Hợp đồng / Phụ lục)</option>
                                        <option value="quote">📊 BG (Bảng báo giá)</option>
                                        <option value="doc">📁 HS liên quan (Nghiệm thu / Biên bản / Hồ sơ khác)</option>
                                        <option value="unc">🏦 UNC (Ủy nhiệm chi / Giấy báo nợ)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold text-dark mb-1">
                                        Chọn file đính kèm (có thể chọn nhiều file) <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="files[]" id="att_files_input" class="form-control form-control-sm rounded-3 shadow-none" multiple required accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.zip,.rar">
                                    <div class="text-muted" style="font-size: 0.72rem; margin-top: 0.2rem;">
                                        Hỗ trợ: PDF, Ảnh (JPG, PNG), Excel, Word. Tối đa 20MB / file.
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold text-dark mb-1">Ghi chú tệp (tùy chọn)</label>
                                    <input type="text" name="notes" id="att_notes" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Ví dụ: Bản scan có chữ ký 2 bên, Báo giá đợt 1...">
                                </div>
                                <div class="col-12 col-md-4 text-end">
                                    <button type="submit" id="btnUploadAttachment" class="btn btn-theme-primary btn-sm rounded-pill px-3 py-1 w-100 shadow-sm d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-upload"></i>
                                        <span>+ Tải Lên Hóa Đơn</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabs lọc danh mục file -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="btn-group btn-group-sm p-1 rounded-pill" style="background-color: #f1f5f9;" role="group">
                        <button type="button" class="btn btn-sm rounded-pill px-3 att-filter-btn active bg-white text-primary fw-semibold shadow-sm" onclick="filterAttachmentList('all', this)">
                            Tất cả (<span id="count_att_all">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0" onclick="filterAttachmentList('invoice', this)">
                            📄 Hóa đơn (<span id="count_att_invoice">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0" onclick="filterAttachmentList('contract', this)">
                            📑 HĐ (<span id="count_att_contract">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0" onclick="filterAttachmentList('quote', this)">
                            📊 BG (<span id="count_att_quote">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0" onclick="filterAttachmentList('doc', this)">
                            📁 HS liên quan (<span id="count_att_doc">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0" onclick="filterAttachmentList('unc', this)">
                            🏦 UNC (<span id="count_att_unc">0</span>)
                        </button>
                    </div>

                    <div class="text-muted small" style="font-size: 0.78rem;">
                        <span id="attTotalFilesNotice">0 tệp tin đính kèm</span>
                    </div>
                </div>

                <!-- Danh sách file đính kèm -->
                <div id="attListLoading" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Đang tải danh sách file...</div>
                </div>

                <div id="attEmptyState" class="text-center py-4 text-muted border rounded-3 bg-light d-none">
                    <i class="bi bi-folder-x fs-2 text-secondary opacity-50 d-block mb-1"></i>
                    <div class="fw-semibold text-dark small">Chưa có file đính kèm nào</div>
                    <div style="font-size: 0.75rem;">Mỗi hóa đơn có tệp đính kèm riêng biệt. Hãy chọn file bên trên để đính kèm.</div>
                </div>

                <div class="table-responsive rounded-3 border" id="attTableWrapper">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Tên Tài Liệu &amp; Kích Thước</th>
                                <th style="width: 8rem;">Phân Loại</th>
                                <th style="width: 9rem;">Thời Gian Tải</th>
                                <th class="text-end" style="width: 8rem;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="attTableBody">
                            <!-- Render bằng JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4 d-flex justify-content-between">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-shield-check text-success me-1"></i> File được lưu trữ an toàn &amp; vĩnh viễn theo mã hóa đơn
                </span>
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

        const concernVal = (inv.is_concerned === false || inv.is_concerned === 0 || inv.is_concerned === '0') ? '0' : '1';
        const editConcernSelect = document.getElementById('edit_inv_is_concerned');
        if (editConcernSelect) {
            editConcernSelect.value = concernVal;
        }

        modal.show();
    }

    function toggleInvoiceConcern(invoiceId) {
        const btn = document.getElementById(`btn-concern-${invoiceId}`);
        if (!btn) return;

        fetch(`/invoices/${invoiceId}/toggle-concern`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_concerned) {
                    btn.innerHTML = `
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; cursor: pointer;">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>Quan tâm</span>
                        </span>
                    `;
                    btn.title = 'Nhấn để đổi sang Không quan tâm';
                } else {
                    btn.innerHTML = `
                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; cursor: pointer;">
                            <i class="bi bi-dash-circle text-muted"></i>
                            <span>Không quan tâm</span>
                        </span>
                    `;
                    btn.title = 'Nhấn để đổi sang Quan tâm';
                }
            } else {
                alert(data.error || 'Có lỗi xảy ra.');
            }
        })
        .catch(err => alert('Lỗi: ' + err.message));
    }

    // --- LOGIC QUẢN LÝ FILE ĐÍNH KÈM HÓA ĐƠN ---
    let currentInvoiceAttachments = [];
    let currentAttachmentFilter = 'all';
    let currentActiveInvoiceId = null;

    function openInvoiceAttachmentsModal(invoiceId, invoiceNumber) {
        currentActiveInvoiceId = invoiceId;
        const modalEl = document.getElementById('modalInvoiceAttachments');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('attModalInvoiceNumber').textContent = `[${invoiceNumber}]`;
        document.getElementById('att_invoice_id').value = invoiceId;
        document.getElementById('uploadAttachmentForm').action = `/invoices/${invoiceId}/attachments`;
        document.getElementById('att_files_input').value = '';
        document.getElementById('att_notes').value = '';

        modal.show();
        loadInvoiceAttachments(invoiceId);
    }

    function loadInvoiceAttachments(invoiceId) {
        const loadingEl = document.getElementById('attListLoading');
        const tableWrapper = document.getElementById('attTableWrapper');
        const emptyState = document.getElementById('attEmptyState');

        loadingEl.classList.remove('d-none');
        tableWrapper.classList.add('d-none');
        emptyState.classList.add('d-none');

        fetch(`/invoices/${invoiceId}/attachments`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            loadingEl.classList.add('d-none');
            currentInvoiceAttachments = data.attachments || [];
            renderAttachmentList();
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            alert('Lỗi tải danh sách file: ' + err.message);
        });
    }

    function renderAttachmentList() {
        const tableWrapper = document.getElementById('attTableWrapper');
        const emptyState = document.getElementById('attEmptyState');
        const tbody = document.getElementById('attTableBody');

        const total = currentInvoiceAttachments.length;
        const countInvoice = currentInvoiceAttachments.filter(a => a.category === 'invoice').length;
        const countContract = currentInvoiceAttachments.filter(a => a.category === 'contract').length;
        const countQuote = currentInvoiceAttachments.filter(a => a.category === 'quote').length;
        const countDoc = currentInvoiceAttachments.filter(a => a.category === 'doc').length;
        const countUnc = currentInvoiceAttachments.filter(a => a.category === 'unc').length;

        document.getElementById('count_att_all').textContent = total;
        document.getElementById('count_att_invoice').textContent = countInvoice;
        document.getElementById('count_att_contract').textContent = countContract;
        document.getElementById('count_att_quote').textContent = countQuote;
        document.getElementById('count_att_doc').textContent = countDoc;
        document.getElementById('count_att_unc').textContent = countUnc;
        document.getElementById('attTotalFilesNotice').textContent = `${total} tệp tin đính kèm`;

        let filtered = currentInvoiceAttachments;
        if (currentAttachmentFilter !== 'all') {
            filtered = currentInvoiceAttachments.filter(a => a.category === currentAttachmentFilter);
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
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-2" style="font-size: 0.75rem;" title="Xóa file" onclick="deleteAttachmentItem(${att.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterAttachmentList(category, btnEl) {
        currentAttachmentFilter = category;
        document.querySelectorAll('.att-filter-btn').forEach(b => {
            b.className = 'btn btn-sm rounded-pill px-2 att-filter-btn text-muted border-0';
        });
        btnEl.className = 'btn btn-sm rounded-pill px-3 att-filter-btn active bg-white text-primary fw-semibold shadow-sm';
        renderAttachmentList();
    }

    function handleUploadAttachments(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnUploadAttachment');
        const invoiceId = document.getElementById('att_invoice_id').value;
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tải...';

        fetch(`/invoices/${invoiceId}/attachments`, {
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
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>+ Tải Lên Hóa Đơn</span>';
            if (data.success) {
                document.getElementById('att_files_input').value = '';
                document.getElementById('att_notes').value = '';
                loadInvoiceAttachments(invoiceId);
            } else {
                alert(data.message || 'Lỗi khi tải file lên.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload"></i> <span>+ Tải Lên Hóa Đơn</span>';
            alert('Lỗi: ' + err.message);
        });
    }

    function deleteAttachmentItem(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa file đính kèm này không?')) return;

        fetch(`/invoice-attachments/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadInvoiceAttachments(currentActiveInvoiceId);
            } else {
                alert(data.error || 'Lỗi xóa file.');
            }
        })
        .catch(err => alert('Lỗi: ' + err.message));
    }
</script>
@endpush
@endsection
