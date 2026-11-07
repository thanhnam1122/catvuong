@extends('layouts.app')

@section('title', 'Sơ Đồ Phân Tích & So Sánh Đầu Vào - Đầu Ra (Tất Cả Doanh Nghiệp)')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header Trang Sơ Đồ Phân Tích -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart-fill text-primary"></i> Sơ Đồ Phân Tích &amp; So Sánh Đầu Vào - Đầu Ra
            </h4>
            <div class="text-muted small">
                Báo cáo tỷ lệ phần trăm (%), phân tích luân chuyển dòng tiền và so sánh đối ứng hóa đơn mua vào &amp; bán ra của tất cả doanh nghiệp
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('invoice.in') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-in-down-left text-danger"></i>
                <span>Xem HĐ Đầu Vào</span>
            </a>
            <a href="{{ route('invoice.out') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-up-right text-success"></i>
                <span>Xem HĐ Xuất Ra</span>
            </a>
            <a href="{{ route('company.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-buildings"></i>
                <span>Danh Sách Doanh Nghiệp ({{ count($partners) }})</span>
            </a>
        </div>
    </div>

    <!-- Thanh Lọc Dữ Liệu Phân Tích -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('dashboard.index') }}" method="GET" class="row g-2 align-items-end" id="filterAnalyticsForm">
                @if($isAdmin)
                    <!-- Chọn CTY (Admin) -->
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label text-muted small mb-1 fw-semibold">
                            <i class="bi bi-building me-1 text-primary"></i> CTY:
                        </label>
                        <select name="company_id" class="form-select form-select-sm rounded-3 shadow-none text-truncate" onchange="this.form.submit()">
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

                <!-- Chọn Doanh Nghiệp Đối Tác -->
                <div class="col-12 col-sm-6 {{ $isAdmin ? 'col-md-3 col-lg-2' : 'col-md-4 col-lg-3' }}">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-briefcase me-1 text-primary"></i> Doanh nghiệp:
                    </label>
                    <select name="partner_id" class="form-select form-select-sm rounded-3 shadow-none text-truncate" onchange="this.form.submit()">
                        <option value="all" {{ ($partnerId == 'all' || empty($partnerId)) ? 'selected' : '' }}>
                            🏢 Tất cả doanh nghiệp ({{ $partners->count() }})
                        </option>
                        @foreach($partners as $p)
                            <option value="{{ $p->id }}" {{ $partnerId == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Phân Loại Theo Dõi -->
                <div class="col-6 col-sm-6 col-md-2 col-lg-auto" style="min-width: 8.5rem;">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-star me-1 text-warning"></i> Phân loại:
                    </label>
                    <select name="concern" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ ($concern == 'all' || $concern === null) ? 'selected' : '' }}>Tất cả phân loại</option>
                        <option value="1" {{ ($concern === '1' || $concern === 1) ? 'selected' : '' }}>⭐ Quan tâm</option>
                        <option value="0" {{ ($concern === '0' || $concern === 0) ? 'selected' : '' }}>⚪ Không quan tâm</option>
                    </select>
                </div>

                <!-- Chọn Kỳ Thời Gian Nhanh -->
                <div class="col-6 col-sm-6 col-md-2 col-lg-auto" style="min-width: 8.5rem;">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-clock-history me-1 text-primary"></i> Kỳ xem:
                    </label>
                    <select name="time_range" id="selectTimeRange" class="form-select form-select-sm rounded-3 shadow-none" onchange="handleTimeRangeChange(this.value)">
                        <option value="all" {{ (empty($timeRange) || $timeRange == 'all') ? 'selected' : '' }}>Toàn bộ thời gian</option>
                        <option value="this_month" {{ $timeRange == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="last_month" {{ $timeRange == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                        <option value="this_quarter" {{ $timeRange == 'this_quarter' ? 'selected' : '' }}>Quý này</option>
                        <option value="this_year" {{ $timeRange == 'this_year' ? 'selected' : '' }}>Năm nay ({{ $targetYear }})</option>
                        <option value="custom" {{ $timeRange == 'custom' ? 'selected' : '' }}>Tùy chọn ngày...</option>
                    </select>
                </div>

                <!-- Chọn khoảng ngày cụ thể -->
                <div class="col-12 col-sm-6 col-md-3 col-lg-auto" id="dateInputsBlock" style="{{ $timeRange != 'custom' && empty($fromDate) && empty($toDate) ? 'display: none;' : '' }}">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-calendar-range me-1 text-primary"></i> Khoảng ngày:
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="from_date" class="form-control rounded-start-3 shadow-none" value="{{ $fromDate ?? '' }}" title="Từ ngày">
                        <span class="input-group-text bg-light text-muted border-start-0 border-end-0">đến</span>
                        <input type="date" name="to_date" class="form-control rounded-end-3 shadow-none" value="{{ $toDate ?? '' }}" title="Đến ngày">
                    </div>
                </div>

                <!-- Nút thao tác lọc & đặt lại -->
                <div class="col-auto d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-theme-primary btn-sm px-3 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm" style="height: 2.125rem;">
                        <i class="bi bi-funnel-fill"></i> <span>Áp Dụng</span>
                    </button>
                    @if(!empty($fromDate) || !empty($toDate) || (!empty($companyId) && $companyId != 'all') || (!empty($partnerId) && $partnerId != 'all') || (!empty($concern) && $concern != 'all') || (!empty($timeRange) && $timeRange != 'all'))
                        <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary btn-sm px-2 rounded-pill d-inline-flex align-items-center justify-content-center" style="height: 2.125rem;" title="Đặt lại toàn bộ bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- 4 Khối Thống Kê Tổng Quan (KPIs & Tỷ Lệ %) -->
    <div class="row g-3 mb-4">
        <!-- 1. Tổng Hóa Đơn Đầu Vào -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border-left: 0.25rem solid #ef4444 !important;">
                <div class="card-body p-3 p-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">
                            <i class="bi bi-box-arrow-in-down-left text-danger me-1"></i> Tổng Đầu Vào
                        </span>
                        <span class="badge rounded-pill bg-danger-subtle text-danger fw-bold fs-6">
                            {{ $inPercent }}%
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark mb-1">
                        {{ number_format($totalIn, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between small text-muted mb-2">
                        <span>Số lượng: <strong class="text-dark">{{ $countIn }}</strong> hóa đơn</span>
                        <span class="text-danger fw-semibold">{{ $paidInPercent }}% đã TT</span>
                    </div>
                    <!-- Thanh tiến độ đã thanh toán -->
                    <div class="progress rounded-pill" style="height: 0.45rem; background-color: #fee2e2;">
                        <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: {{ min(100, $paidInPercent) }}%;" aria-valuenow="{{ $paidInPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem; margin-top: 0.35rem;">
                        <span>Đã TT: <strong>{{ number_format($paidIn, 0, ',', '.') }} đ</strong></span>
                        <span>Còn nợ: <strong>{{ number_format($remainIn, 0, ',', '.') }} đ</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Tổng Hóa Đơn Xuất Ra -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border-left: 0.25rem solid #0284c7 !important;">
                <div class="card-body p-3 p-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">
                            <i class="bi bi-box-arrow-up-right text-primary me-1"></i> Tổng Đầu Ra
                        </span>
                        <span class="badge rounded-pill bg-primary-subtle text-primary fw-bold fs-6">
                            {{ $outPercent }}%
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark mb-1">
                        {{ number_format($totalOut, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between small text-muted mb-2">
                        <span>Số lượng: <strong class="text-dark">{{ $countOut }}</strong> hóa đơn</span>
                        <span class="text-primary fw-semibold">{{ $paidOutPercent }}% đã thu</span>
                    </div>
                    <!-- Thanh tiến độ đã thu tiền -->
                    <div class="progress rounded-pill" style="height: 0.45rem; background-color: #e0f2fe;">
                        <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ min(100, $paidOutPercent) }}%;" aria-valuenow="{{ $paidOutPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem; margin-top: 0.35rem;">
                        <span>Đã thu: <strong>{{ number_format($paidOut, 0, ',', '.') }} đ</strong></span>
                        <span>Phải thu: <strong>{{ number_format($remainOut, 0, ',', '.') }} đ</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Chênh Lệch Đầu Ra - Đầu Vào -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border-left: 0.25rem solid {{ $netDiff >= 0 ? '#10b981' : '#f59e0b' }} !important;">
                <div class="card-body p-3 p-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">
                            <i class="bi bi-arrow-left-right text-success me-1"></i> Chênh Lệch (Xuất - Vào)
                        </span>
                        <span class="badge rounded-pill {{ $netDiff >= 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fw-bold fs-6">
                            {{ $diffPercent >= 0 ? '+' : '' }}{{ $diffPercent }}%
                        </span>
                    </div>
                    <div class="fs-4 fw-bold {{ $netDiff >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                        {{ $netDiff >= 0 ? '+' : '' }}{{ number_format($netDiff, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                    </div>
                    <div class="small text-muted mb-2">
                        Trạng thái:
                        @if($netDiff > 0)
                            <strong class="text-success"><i class="bi bi-graph-up me-1"></i>Thặng dư (Doanh thu vượt chi phí)</strong>
                        @elseif($netDiff < 0)
                            <strong class="text-danger"><i class="bi bi-graph-down me-1"></i>Chi phí đầu vào cao hơn</strong>
                        @else
                            <strong class="text-secondary">Cân bằng 0 đ</strong>
                        @endif
                    </div>
                    <div class="p-2 rounded-3 bg-light" style="font-size: 0.75rem;">
                        Tỷ số Xuất/Vào: <strong>{{ $totalIn > 0 ? round($totalOut / $totalIn, 2) : ($totalOut > 0 ? '∞' : '0') }}x</strong>
                        (Mỗi 1đ chi phí tạo ra {{ $totalIn > 0 ? round($totalOut / $totalIn, 2) : ($totalOut > 0 ? '∞' : '0') }}đ doanh thu)
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Tổng Luân Chuyển Dòng Tiền & Quy Mô Đối Tác -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border-left: 0.25rem solid #8b5cf6 !important;">
                <div class="card-body p-3 p-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold small text-uppercase">
                            <i class="bi bi-cash-stack text-purple me-1" style="color: #8b5cf6;"></i> Tổng Luân Chuyển
                        </span>
                        <span class="badge rounded-pill bg-light text-secondary fw-bold">
                            100% Volume
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark mb-1">
                        {{ number_format($totalVolume, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between small text-muted mb-2">
                        <span>Doanh nghiệp giao dịch:</span>
                        <strong class="text-primary">{{ $activePartnerCount }} / {{ count($partners) }} DN</strong>
                    </div>
                    <div class="p-2 rounded-3 bg-light text-muted" style="font-size: 0.75rem;">
                        <span>Tổng số HĐ đã xử lý: <strong>{{ $countIn + $countOut }} HĐ</strong></span>
                        <span class="float-end">Đã xử lý thanh toán: <strong>{{ $totalVolume > 0 ? round((($paidIn + $paidOut) / $totalVolume) * 100, 1) : 0 }}%</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG BIỂU ĐỒ TỶ TRỌNG: CƠ CẤU ĐẦU VÀO/ĐẦU RA & TỶ TRỌNG THEO TỪNG DOANH NGHIỆP -->
    <div class="row g-3 mb-4">
        <!-- 1. Cơ Cấu Tỷ Lệ % Đầu Vào vs Đầu Ra -->
        <div class="col-12 col-xl-4 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart text-primary"></i> Cơ Cấu Đầu Vào vs Đầu Ra
                    </h6>
                    <span class="text-muted small">Tỷ trọng phần trăm trên tổng luân chuyển tiền tệ</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column align-items-center justify-content-between">
                    @if($totalVolume > 0)
                        <div style="position: relative; width: 100%; max-width: 15rem; height: 15rem;">
                            <canvas id="chartInOutRatio"></canvas>
                        </div>
                        <!-- Bảng số liệu chi tiết phía dưới biểu đồ -->
                        <div class="w-100 mt-3 pt-3 border-top">
                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-danger-subtle">
                                        <div class="small text-danger fw-bold mb-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i> Đầu Vào
                                        </div>
                                        <div class="fw-bold text-dark fs-6">{{ $inPercent }}%</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ number_format($totalIn, 0, ',', '.') }} đ</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-primary-subtle">
                                        <div class="small text-primary fw-bold mb-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i> Đầu Ra
                                        </div>
                                        <div class="fw-bold text-dark fs-6">{{ $outPercent }}%</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ number_format($totalOut, 0, ',', '.') }} đ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-pie-chart fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Chưa có dữ liệu hóa đơn nào trong khoảng lọc này.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Cơ Cấu Tỷ Trọng % Đầu Vào Theo Doanh Nghiệp -->
        <div class="col-12 col-xl-4 col-md-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-danger"></i> Tỷ Trọng % Đầu Vào Theo DN
                    </h6>
                    <span class="text-muted small">Doanh nghiệp nào chiếm bao nhiêu % chi phí mua hàng</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column align-items-center justify-content-center">
                    @if(count($pieInData) > 0)
                        <div style="position: relative; width: 100%; height: 18rem;">
                            <canvas id="chartPieIn"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Chưa có hóa đơn đầu vào nào.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Cơ Cấu Tỷ Trọng % Đầu Ra Theo Doanh Nghiệp -->
        <div class="col-12 col-xl-4 col-md-12">
            <div class="card border-0 rounded-4 shadow-sm h-100 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-primary"></i> Tỷ Trọng % Đầu Ra Theo DN
                    </h6>
                    <span class="text-muted small">Doanh nghiệp nào đóng góp bao nhiêu % doanh thu bán ra</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column align-items-center justify-content-center">
                    @if(count($pieOutData) > 0)
                        <div style="position: relative; width: 100%; height: 18rem;">
                            <canvas id="chartPieOut"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-box-arrow-up-right fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Chưa có hóa đơn xuất ra nào.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG BIỂU ĐỒ 2: XU HƯỚNG 12 THÁNG -->
    <div class="row g-3 mb-4">
        <!-- Biểu Đồ Xu Hướng 12 Tháng Trong Năm -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
                <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up-arrow text-primary"></i> Biến Động Dòng Tiền Đầu Vào &amp; Đầu Ra Theo Tháng (Năm {{ $targetYear }})
                        </h6>
                        <span class="text-muted small">Biểu đồ so sánh nhịp độ mua hàng và bán hàng qua 12 tháng</span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="position: relative; width: 100%; height: 18rem;">
                        <canvas id="chartTimelineMonths"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG SO SÁNH & THỐNG KÊ CHI TIẾT TẤT CẢ DOANH NGHIỆP -->
    <div class="card border-0 rounded-4 shadow-sm bg-white mb-4" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i> Bảng Phân Tích &amp; So Sánh Chi Tiết Từng Doanh Nghiệp
                </h6>
                <span class="text-muted small">Tổng hợp số tiền, tỷ lệ % tỷ trọng, chênh lệch và tình trạng thanh toán của từng đối tác</span>
            </div>
            <!-- Ô tìm nhanh đối tác trong bảng -->
            <div style="width: 16rem;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="tableQuickSearch" class="form-control rounded-end-pill shadow-none" placeholder="Tìm tên đối tác trong bảng..." onkeyup="filterAnalyticsTable(this.value)">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="analyticsTable">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4 py-3" style="width: 3.5rem;">STT</th>
                            <th class="py-3" style="min-width: 14rem;">Doanh Nghiệp / Đối Tác</th>
                            <th class="py-3 text-end" style="min-width: 12rem;">
                                <i class="bi bi-box-arrow-in-down-left text-danger me-1"></i> Đầu Vào (Mua)
                            </th>
                            <th class="py-3 text-end" style="min-width: 12rem;">
                                <i class="bi bi-box-arrow-up-right text-primary me-1"></i> Đầu Ra (Bán)
                            </th>
                            <th class="py-3 text-end" style="min-width: 11rem;">
                                <i class="bi bi-arrow-left-right text-success me-1"></i> Chênh Lệch
                            </th>
                            <th class="py-3" style="min-width: 12rem;">Tiến Độ Thanh Toán</th>
                            <th class="pe-4 py-3 text-center" style="width: 7rem;">Xem HĐ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partnerStats as $index => $item)
                            @php
                                $p = $item['partner'];
                            @endphp
                            <tr class="partner-row {{ !$item['has_invoices'] ? 'opacity-75' : '' }}">
                                <td class="ps-4 fw-semibold text-muted small">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark partner-name">{{ $p->name }}</div>
                                    <div class="text-muted small d-flex flex-wrap align-items-center gap-2">
                                        @if($p->tax_code)
                                            <span>MST: <code class="text-secondary">{{ $p->tax_code }}</code></span>
                                        @endif
                                        @if($p->type === 'supplier')
                                            <span class="badge bg-warning-subtle text-warning" style="font-size: 0.65rem;">Nhà cung cấp</span>
                                        @elseif($p->type === 'customer')
                                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">Khách hàng</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">Cả hai chiều</span>
                                        @endif
                                        @if($isAdmin && $p->company)
                                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">{{ $p->company->name }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Cột Đầu Vào -->
                                <td class="text-end">
                                    @if($item['in_total'] > 0)
                                        <div class="fw-bold text-danger">
                                            {{ number_format($item['in_total'], 0, ',', '.') }} đ
                                        </div>
                                        <div class="small text-muted d-flex align-items-center justify-content-end gap-1">
                                            <span>{{ $item['in_count'] }} HĐ</span>
                                            <span>•</span>
                                            <span class="badge bg-danger-subtle text-danger">{{ $item['in_percent'] }}%</span>
                                        </div>
                                        <div class="progress rounded-pill mt-1 ms-auto" style="height: 0.25rem; width: 6rem; background-color: #fee2e2;">
                                            <div class="progress-bar bg-danger rounded-pill" style="width: {{ min(100, $item['in_percent']) }}%;"></div>
                                        </div>
                                    @else
                                        <span class="text-muted small">---</span>
                                    @endif
                                </td>

                                <!-- Cột Đầu Ra -->
                                <td class="text-end">
                                    @if($item['out_total'] > 0)
                                        <div class="fw-bold text-primary">
                                            {{ number_format($item['out_total'], 0, ',', '.') }} đ
                                        </div>
                                        <div class="small text-muted d-flex align-items-center justify-content-end gap-1">
                                            <span>{{ $item['out_count'] }} HĐ</span>
                                            <span>•</span>
                                            <span class="badge bg-primary-subtle text-primary">{{ $item['out_percent'] }}%</span>
                                        </div>
                                        <div class="progress rounded-pill mt-1 ms-auto" style="height: 0.25rem; width: 6rem; background-color: #e0f2fe;">
                                            <div class="progress-bar bg-primary rounded-pill" style="width: {{ min(100, $item['out_percent']) }}%;"></div>
                                        </div>
                                    @else
                                        <span class="text-muted small">---</span>
                                    @endif
                                </td>

                                <!-- Cột Chênh Lệch -->
                                <td class="text-end">
                                    @if($item['has_invoices'])
                                        <div class="fw-bold {{ $item['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $item['balance'] >= 0 ? '+' : '' }}{{ number_format($item['balance'], 0, ',', '.') }} đ
                                        </div>
                                        <span class="small text-muted" style="font-size: 0.72rem;">
                                            {{ $item['balance'] >= 0 ? 'Dư bán ra' : 'Vượt mua vào' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">0 đ</span>
                                    @endif
                                </td>

                                <!-- Cột Tiến Độ Thanh Toán -->
                                <td>
                                    @if($item['in_total'] > 0)
                                        <div class="d-flex align-items-center justify-content-between small text-muted" style="font-size: 0.75rem;">
                                            <span>Đầu vào trả:</span>
                                            <span class="fw-bold text-dark">{{ $item['in_paid_rate'] }}%</span>
                                        </div>
                                        <div class="progress rounded-pill mb-1" style="height: 0.3rem; background-color: #f1f5f9;">
                                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $item['in_paid_rate'] }}%;"></div>
                                        </div>
                                    @endif

                                    @if($item['out_total'] > 0)
                                        <div class="d-flex align-items-center justify-content-between small text-muted" style="font-size: 0.75rem;">
                                            <span>Đầu ra thu:</span>
                                            <span class="fw-bold text-dark">{{ $item['out_paid_rate'] }}%</span>
                                        </div>
                                        <div class="progress rounded-pill" style="height: 0.3rem; background-color: #f1f5f9;">
                                            <div class="progress-bar bg-primary rounded-pill" style="width: {{ $item['out_paid_rate'] }}%;"></div>
                                        </div>
                                    @endif

                                    @if(!$item['has_invoices'])
                                        <span class="text-muted small" style="font-size: 0.75rem;">Chưa phát sinh</span>
                                    @endif
                                </td>

                                <!-- Thao tác: Xem HĐ -->
                                <td class="pe-4 text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoice.in', ['partner_id' => $p->id]) }}" class="btn btn-outline-danger btn-sm py-1 px-2" title="Xem hóa đơn đầu vào từ {{ $p->name }}">
                                            <i class="bi bi-box-arrow-in-down-left"></i> Vào
                                        </a>
                                        <a href="{{ route('invoice.out', ['partner_id' => $p->id]) }}" class="btn btn-outline-primary btn-sm py-1 px-2" title="Xem hóa đơn xuất ra cho {{ $p->name }}">
                                            <i class="bi bi-box-arrow-up-right"></i> Ra
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-buildings fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Không có doanh nghiệp nào phù hợp với bộ lọc hiện tại.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Xử lý ẩn/hiện ô chọn ngày tùy biến khi đổi select kỳ thời gian
    function handleTimeRangeChange(val) {
        const dateBlock = document.getElementById('dateInputsBlock');
        if (val === 'custom') {
            dateBlock.style.display = 'block';
        } else {
            dateBlock.style.display = 'none';
            // Tự động submit nếu không phải custom
            document.getElementById('filterAnalyticsForm').submit();
        }
    }

    // Tìm kiếm nhanh trong bảng chi tiết
    function filterAnalyticsTable(keyword) {
        const query = keyword.toLowerCase().trim();
        const rows = document.querySelectorAll('#analyticsTable tbody tr.partner-row');
        rows.forEach(row => {
            const nameEl = row.querySelector('.partner-name');
            if (!nameEl) return;
            const text = nameEl.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Cấu hình định dạng tiền tệ VND cho tooltip Chart.js
    const formatCurrencyVND = function(val) {
        return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
    };

    document.addEventListener('DOMContentLoaded', function() {
        // 1. BIỂU ĐỒ DONUT: TỶ LỆ ĐẦU VÀO VS ĐẦU RA
        const ctxRatio = document.getElementById('chartInOutRatio');
        if (ctxRatio) {
            new Chart(ctxRatio, {
                type: 'doughnut',
                data: {
                    labels: ['Hóa Đơn Đầu Vào', 'Hóa Đơn Xuất Ra'],
                    datasets: [{
                        data: [{{ $totalIn }}, {{ $totalOut }}],
                        backgroundColor: ['#ef4444', '#0284c7'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.raw || 0;
                                    const total = {{ $totalVolume }};
                                    const percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ' ' + context.label + ': ' + formatCurrencyVND(val) + ' (' + percent + '%)';
                                }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // Tên đầy đủ của các doanh nghiệp dùng khi hover tooltip
        const pieInFullNames = {!! json_encode($pieInFullNames ?? []) !!};
        const pieOutFullNames = {!! json_encode($pieOutFullNames ?? []) !!};

        // 2. BIỂU ĐỒ TỶ TRỌNG % ĐẦU VÀO THEO DOANH NGHIỆP
        const ctxPieIn = document.getElementById('chartPieIn');
        if (ctxPieIn) {
            new Chart(ctxPieIn, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($pieInLabels) !!},
                    datasets: [{
                        data: {!! json_encode($pieInData) !!},
                        backgroundColor: [
                            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#94a3b8'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: function(contexts) {
                                    if (!contexts.length) return '';
                                    const idx = contexts[0].dataIndex;
                                    return pieInFullNames[idx] || contexts[0].label;
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const fullName = pieInFullNames[idx] || context.label;
                                    const val = context.raw || 0;
                                    const total = {{ $totalIn }};
                                    const percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ' ' + fullName + ': ' + formatCurrencyVND(val) + ' (' + percent + '%)';
                                }
                            }
                        }
                    },
                    cutout: '55%'
                }
            });
        }

        // 3. BIỂU ĐỒ TỶ TRỌNG % ĐẦU RA THEO DOANH NGHIỆP
        const ctxPieOut = document.getElementById('chartPieOut');
        if (ctxPieOut) {
            new Chart(ctxPieOut, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($pieOutLabels) !!},
                    datasets: [{
                        data: {!! json_encode($pieOutData) !!},
                        backgroundColor: [
                            '#0284c7', '#06b6d4', '#10b981', '#3b82f6', '#6366f1', '#94a3b8'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: function(contexts) {
                                    if (!contexts.length) return '';
                                    const idx = contexts[0].dataIndex;
                                    return pieOutFullNames[idx] || contexts[0].label;
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const fullName = pieOutFullNames[idx] || context.label;
                                    const val = context.raw || 0;
                                    const total = {{ $totalOut }};
                                    const percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ' ' + fullName + ': ' + formatCurrencyVND(val) + ' (' + percent + '%)';
                                }
                            }
                        }
                    },
                    cutout: '55%'
                }
            });
        }

        // 5. BIỂU ĐỒ XU HƯỚNG 12 THÁNG
        const ctxMonths = document.getElementById('chartTimelineMonths');
        if (ctxMonths) {
            const monthsLabels = {!! json_encode(array_column($monthlyData, 'label')) !!};
            const monthsIn = {!! json_encode(array_column($monthlyData, 'in')) !!};
            const monthsOut = {!! json_encode(array_column($monthlyData, 'out')) !!};

            new Chart(ctxMonths, {
                type: 'bar',
                data: {
                    labels: monthsLabels,
                    datasets: [
                        {
                            label: 'Đầu Vào (Mua)',
                            data: monthsIn,
                            backgroundColor: 'rgba(239, 68, 68, 0.85)',
                            borderRadius: 4
                        },
                        {
                            label: 'Đầu Ra (Bán)',
                            data: monthsOut,
                            backgroundColor: 'rgba(2, 132, 199, 0.85)',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + formatCurrencyVND(context.raw || 0);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1e9) return (value / 1e9).toFixed(1) + ' tỷ';
                                    if (value >= 1e6) return (value / 1e6).toFixed(0) + ' tr';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

