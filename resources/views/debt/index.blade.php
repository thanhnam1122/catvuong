@extends('layouts.app')

@section('title', 'Quản Lý Công Nợ - Phải Thu & Phải Trả')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Thanh Lọc Đa Công Ty & Tìm Kiếm Nhanh (Bo góc rounded-4 mượt mà) -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 bg-white" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('debt.index') }}" method="GET" class="row g-2 align-items-end">
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

                <!-- Lọc theo loại nợ -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-arrow-left-right me-1 text-primary"></i> Loại công nợ:
                    </label>
                    <select name="type" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ empty($type) || $type == 'all' ? 'selected' : '' }}>Tất cả các loại</option>
                        <option value="receivable" {{ $type == 'receivable' ? 'selected' : '' }}>📥 Công Nợ Phải Thu</option>
                        <option value="payable" {{ $type == 'payable' ? 'selected' : '' }}>📤 Công Nợ Phải Trả</option>
                    </select>
                </div>

                <!-- Lọc theo trạng thái -->
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-funnel me-1 text-primary"></i> Trạng thái:
                    </label>
                    <select name="status" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="all" {{ empty($status) || $status == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>⏳ Chưa thanh toán</option>
                        <option value="partial" {{ $status == 'partial' ? 'selected' : '' }}>🔄 Thanh toán 1 phần</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>✅ Đã thanh toán</option>
                        <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>🚨 Quá hạn thanh toán</option>
                    </select>
                </div>

                <!-- Tìm kiếm -->
                <div class="col-12 col-md-8 col-lg-4">
                    <label class="form-label text-muted small mb-1 fw-semibold">
                        <i class="bi bi-search me-1 text-primary"></i> Tìm kiếm:
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill ps-3 shadow-none" placeholder="Tên đối tác, mã phiếu, SĐT..." value="{{ $search ?? '' }}">
                        <button class="btn btn-soft-primary px-3 {{ (empty($search) && (empty($companyId) || $companyId == 'all') && (empty($type) || $type == 'all') && (empty($status) || $status == 'all')) ? 'rounded-end-pill' : '' }}" type="submit">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                        @if(!empty($search) || (!empty($companyId) && $companyId != 'all') || (!empty($type) && $type != 'all') || (!empty($status) && $status != 'all'))
                            <a href="{{ route('debt.index') }}" class="btn btn-outline-secondary rounded-end-pill px-2 d-flex align-items-center" title="Đặt lại bộ lọc">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Nút thêm nhanh (Căn thẳng hàng chuẩn xác với các ô input) -->
                <div class="col-12 col-md-4 col-lg-1">
                    <button type="button" class="btn btn-theme-primary btn-sm w-100 rounded-pill d-flex align-items-center justify-content-center gap-1 shadow-sm" style="height: 2.125rem;" data-bs-toggle="modal" data-bs-target="#modalCreateDebt" title="Tạo khoản nợ mới">
                        <i class="bi bi-plus-lg"></i> <span>Thêm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4 THẺ THỐNG KÊ TỔNG QUAN (Bo góc rounded-4 mềm mại) -->
    <div class="row g-3 mb-4">
        <!-- 1. Công Nợ Phải Thu -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Công Nợ Phải Thu</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-box-arrow-in-down-left"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-primary mb-1">
                    {{ number_format($remainingReceivable, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="d-flex justify-content-between small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    <span>Tổng phát sinh: {{ number_format($totalReceivable, 0, ',', '.') }} đ</span>
                    <span class="text-success fw-medium">Đã thu: {{ number_format($paidReceivable, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- 2. Công Nợ Phải Trả -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Công Nợ Phải Trả</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #fef3c7; color: #d97706;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-warning mb-1" style="color: #d97706 !important;">
                    {{ number_format($remainingPayable, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="d-flex justify-content-between small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    <span>Tổng nợ gốc: {{ number_format($totalPayable, 0, ',', '.') }} đ</span>
                    <span class="text-success fw-medium">Đã trả: {{ number_format($paidPayable, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- 3. Chênh Lệch Công Nợ -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Chênh Lệch Thu - Trả</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: {{ $netDebt >= 0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $netDebt >= 0 ? '#059669' : '#dc2626' }};">
                        <i class="bi bi-scale"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold mb-1 {{ $netDebt >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $netDebt >= 0 ? '+' : '' }}{{ number_format($netDebt, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">đ</span>
                </div>
                <div class="small text-muted border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    @if($netDebt >= 0)
                        <i class="bi bi-check2-circle text-success me-1"></i> Tổng khoản phải thu lớn hơn tổng khoản phải trả
                    @else
                        <i class="bi bi-exclamation-circle text-danger me-1"></i> Cần lưu ý: Tổng khoản phải trả lớn hơn phải thu
                    @endif
                </div>
            </div>
        </div>

        <!-- 4. Khoản Quá Hạn -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 p-md-4 h-100 rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.03em;">Quá Hạn Thanh Toán</span>
                    <div class="stat-icon-wrapper rounded-3" style="background-color: #fee2e2; color: #dc2626;">
                        <i class="bi bi-bell"></i>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-danger mb-1">
                    {{ $overdueCount }} <span class="fs-6 fw-normal text-muted">khoản nợ</span>
                </div>
                <div class="d-flex justify-content-between align-items-center small border-top pt-2 mt-2" style="font-size: 0.8rem;">
                    <span class="text-muted">Cần theo dõi &amp; đôn đốc</span>
                    @if($overdueCount > 0)
                        <a href="{{ route('debt.index', ['status' => 'overdue']) }}" class="text-danger fw-semibold text-decoration-none">
                            Xem ngay <i class="bi bi-arrow-right"></i>
                        </a>
                    @else
                        <span class="text-success fw-medium">Tiến độ tốt</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH CÔNG NỢ CHI TIẾT (Bo góc rounded-4, overflow-hidden) -->
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4" style="border: 0.0625rem solid var(--border-soft) !important;">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border-soft) !important;">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt-cutoff me-1 text-primary"></i> Danh Sách Khoản Công Nợ
                </h5>
                <span class="text-muted small">
                    Hiển thị {{ $debts->count() }} / {{ $debts->total() }} chứng từ công nợ
                </span>
            </div>

            <!-- Phân loại nhanh bằng tab bo tròn dạng pill -->
            <div class="btn-group btn-group-sm p-1 rounded-pill" style="background-color: #f1f5f9;" role="group">
                <a href="{{ route('debt.index', array_merge(request()->query(), ['type' => 'all'])) }}" class="btn rounded-pill px-3 {{ empty($type) || $type == 'all' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    Tất cả ({{ $debts->total() }})
                </a>
                <a href="{{ route('debt.index', array_merge(request()->query(), ['type' => 'receivable'])) }}" class="btn rounded-pill px-3 {{ $type == 'receivable' ? 'btn-white bg-white text-primary shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    📥 Phải Thu
                </a>
                <a href="{{ route('debt.index', array_merge(request()->query(), ['type' => 'payable'])) }}" class="btn rounded-pill px-3 {{ $type == 'payable' ? 'btn-white bg-white text-warning shadow-sm fw-semibold' : 'text-muted border-0' }}">
                    📤 Phải Trả
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle">
                <thead>
                    <tr>
                        <th style="width: 7.5rem;">Mã Phiếu</th>
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <th style="width: 10.5rem;">Đơn Vị Quản Lý</th>
                        @endif
                        <th style="width: 14rem;">Đối Tác / Khách Hàng</th>
                        <th style="width: 7.5rem;">Phân Loại</th>
                        <th class="text-end" style="width: 8.5rem;">Số Tiền Nợ</th>
                        <th class="text-end" style="width: 8.5rem;">Đã Thanh Toán</th>
                        <th class="text-end" style="width: 8.5rem;">Còn Lại</th>
                        <th style="width: 8rem;">Hạn Trả</th>
                        <th class="text-center" style="width: 8rem;">Trạng Thái</th>
                        <th class="text-end" style="width: 12rem;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debts as $item)
                        @php
                            $isReceivable = $item->type === 'receivable';
                            $remaining = $item->remaining_amount;
                            $isOverdue = $item->due_date && \Carbon\Carbon::parse($item->due_date)->isPast() && $remaining > 0;
                            $overdueDays = $item->due_date ? (int)abs(\Carbon\Carbon::parse($item->due_date)->diffInDays(now())) : 0;
                        @endphp
                        <tr>
                            <!-- Mã phiếu -->
                            <td>
                                <span class="fw-semibold text-secondary font-monospace" style="font-size: 0.85rem;">
                                    {{ $item->code }}
                                </span>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ \Carbon\Carbon::parse($item->issue_date)->format('d/m/Y') }}
                                </div>
                            </td>

                            <!-- Đơn vị quản lý (Chỉ hiển thị cho Quản trị viên) -->
                            @if(Auth::check() && Auth::user()->isAdmin())
                                <td>
                                    <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 10rem;" title="{{ $item->company->name ?? '' }}">
                                        {{ $item->company->name ?? '---' }}
                                    </span>
                                    @if(!empty($item->company->code))
                                        <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.68rem;">
                                            {{ $item->company->code }}
                                        </span>
                                    @endif
                                </td>
                            @endif

                            <!-- Đối tác & Diễn giải (Gọn gàng) -->
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 13rem;" title="{{ $item->partner->name ?? '' }}">
                                    {{ $item->partner->name ?? '---' }}
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 13rem;" title="{{ $item->title }}">
                                    {{ $item->title }}
                                </div>
                                @if(!empty($item->partner->phone))
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        <i class="bi bi-telephone text-primary"></i> {{ $item->partner->phone }}
                                    </div>
                                @endif
                            </td>

                            <!-- Phân loại nợ (Phải Thu / Phải Trả) -->
                            <td>
                                @if($isReceivable)
                                    <span class="badge badge-soft-info d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill">
                                        <i class="bi bi-box-arrow-in-down-left"></i> Phải Thu
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill">
                                        <i class="bi bi-box-arrow-up-right"></i> Phải Trả
                                    </span>
                                @endif
                            </td>

                            <!-- Tiền nợ gốc -->
                            <td class="text-end fw-semibold text-dark">
                                {{ number_format($item->amount, 0, ',', '.') }} đ
                            </td>

                            <!-- Đã thanh toán -->
                            <td class="text-end text-success fw-medium">
                                {{ number_format($item->paid_amount, 0, ',', '.') }} đ
                                @if($item->payments->count() > 0)
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-muted shadow-none" title="Xem lịch sử thanh toán" onclick="showPaymentHistory('{{ $item->id }}', '{{ $item->code }}')">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                @endif
                            </td>

                            <!-- Còn lại -->
                            <td class="text-end fw-bold {{ $remaining > 0 ? ($isReceivable ? 'text-primary' : 'text-danger') : 'text-muted' }}">
                                {{ number_format($remaining, 0, ',', '.') }} đ
                            </td>

                            <!-- Hạn trả -->
                            <td>
                                @if($item->due_date)
                                    <div class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.85rem;">
                                        {{ \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" style="font-size: 0.68rem;">
                                            Quá hạn {{ $overdueDays }} ngày
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">Không hẹn</span>
                                @endif
                            </td>

                            <!-- Trạng thái -->
                            <td class="text-center">
                                @if($remaining <= 0)
                                    <span class="badge badge-soft-success py-1 px-2 rounded-pill">
                                        <i class="bi bi-check2"></i> Đã thanh toán
                                    </span>
                                @elseif($isOverdue)
                                    <span class="badge badge-soft-danger py-1 px-2 rounded-pill">
                                        <i class="bi bi-exclamation-triangle"></i> Quá hạn
                                    </span>
                                @elseif($item->paid_amount > 0)
                                    <span class="badge badge-soft-info py-1 px-2 rounded-pill">
                                        <i class="bi bi-arrow-repeat"></i> Trả 1 phần
                                    </span>
                                @else
                                    <span class="badge badge-soft-warning py-1 px-2 rounded-pill">
                                        <i class="bi bi-hourglass-split"></i> Chưa thanh toán
                                    </span>
                                @endif
                            </td>

                            <!-- Thao tác: Nút Thanh toán dài ra, nằm gọn gàng trên 1 dòng -->
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    <!-- Nút Thanh Toán (Dài ra, hiển thị rõ ràng trên một dòng) -->
                                    @if($remaining > 0)
                                        <button type="button" class="btn btn-sm btn-theme-primary px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1 text-nowrap shadow-sm" title="Ghi nhận thanh toán" onclick="openPaymentModal({{ json_encode($item) }})">
                                            <i class="bi bi-credit-card-2-front"></i>
                                            <span>Thanh toán</span>
                                        </button>
                                    @else
                                        <span class="badge bg-light text-success border py-1 px-2 rounded-pill small">
                                            <i class="bi bi-check2"></i> Đã xong
                                        </span>
                                    @endif

                                    <!-- Nút In / Đối soát -->
                                    <a href="{{ route('debt.statement', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Xem biên bản đối soát & in ấn">
                                        <i class="bi bi-printer"></i>
                                    </a>

                                    <!-- Dropdown hành động khác -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 rounded-circle p-1 shadow-none" style="width: 2rem; height: 2rem;" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2">
                                            <li>
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick="openEditModal({{ json_encode($item) }})">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Sửa thông tin
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item small py-2 rounded-3" type="button" onclick="showPaymentHistory('{{ $item->id }}', '{{ $item->code }}')">
                                                    <i class="bi bi-clock-history me-2 text-secondary"></i> Lịch sử thanh toán ({{ $item->payments->count() }})
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('debt.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa chứng từ công nợ [{{ $item->code }}] này không?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item small text-danger py-2 rounded-3">
                                                        <i class="bi bi-trash3 me-2"></i> Xóa chứng từ
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
                            <td colspan="{{ (Auth::check() && Auth::user()->isAdmin()) ? 10 : 9 }}" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50"></i>
                                </div>
                                <h6 class="fw-semibold text-dark">Chưa có khoản công nợ nào phù hợp</h6>
                                <p class="small text-muted mb-3">Bạn có thể tạo mới khoản công nợ hoặc điều chỉnh lại bộ lọc tìm kiếm.</p>
                                <button type="button" class="btn btn-theme-primary btn-sm rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCreateDebt">
                                    <i class="bi bi-plus-lg me-1"></i> Tạo Khoản Công Nợ Mới
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($debts->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top" style="border-color: var(--border-soft) !important;">
                {{ $debts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: THÊM MỚI KHOẢN CÔNG NỢ (Bo góc rounded-4 mượt mà) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCreateDebt" tabindex="-1" aria-labelledby="modalCreateDebtLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalCreateDebtLabel">
                    <span class="navbar-brand-badge" style="width: 2rem; height: 2rem; font-size: 1rem;">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                    Tạo Mới Khoản Công Nợ
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('debt.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Loại công nợ: Phải thu hay Phải trả -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Tính chất công nợ <span class="text-danger">*</span>
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type_receivable" value="receivable" checked>
                                    <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center rounded-3" for="type_receivable">
                                        <i class="bi bi-box-arrow-in-down-left fs-5 mb-1"></i>
                                        <span class="fw-bold">Khoản Phải Thu</span>
                                        <span class="small opacity-75" style="font-size: 0.75rem;">Khách hàng / đối tác chưa thanh toán</span>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type_payable" value="payable">
                                    <label class="btn btn-outline-warning w-100 py-2 d-flex flex-column align-items-center rounded-3" for="type_payable">
                                        <i class="bi bi-box-arrow-up-right fs-5 mb-1"></i>
                                        <span class="fw-bold text-dark">Khoản Phải Trả</span>
                                        <span class="small text-muted" style="font-size: 0.75rem;">Mua hàng / nhận dịch vụ chưa thanh toán</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Thuộc Đơn vị / Công ty quản lý -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Đơn vị / Công ty quản lý <span class="text-danger">*</span>
                            </label>
                            <select name="company_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }} {{ $comp->code ? '('.$comp->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Chọn công ty đại diện trong giao dịch này</div>
                        </div>

                        <!-- Đối tác / Khách hàng -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Đối tác / Khách hàng <span class="text-danger">*</span>
                            </label>
                            <select name="partner_id" id="create_partner_select" class="form-select rounded-3 shadow-none">
                                <option value="">-- Chọn đối tác đã có --</option>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}" data-company-id="{{ $p->company_id }}">{{ $p->name }} {{ $p->phone ? '('.$p->phone.')' : '' }}</option>
                                @endforeach
                                <option value="NEW">+ Thêm đối tác mới ngay...</option>
                            </select>
                            <div class="form-text small">Nếu là đối tác mới, chọn "+ Thêm đối tác mới ngay"</div>
                        </div>

                        <!-- Trường nhập nhanh đối tác mới (mặc định ẩn) -->
                        <div class="col-12 p-3 bg-light rounded-3 border d-none" id="quickNewPartnerBox">
                            <div class="fw-semibold text-dark small mb-2">
                                <i class="bi bi-person-plus text-primary"></i> Nhập nhanh thông tin đối tác mới:
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-7">
                                    <input type="text" name="new_partner_name" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Tên công ty hoặc khách hàng...">
                                </div>
                                <div class="col-12 col-md-5">
                                    <input type="text" name="new_partner_phone" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Số điện thoại liên hệ...">
                                </div>
                            </div>
                        </div>

                        <!-- Tiêu đề / Nội dung khoản nợ -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Nội dung / Diễn giải công nợ <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Thi công hệ thống văn phòng, Tiền nhập lô linh kiện máy, Hóa đơn #102..." required>
                        </div>

                        <!-- Số tiền nợ gốc -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Số tiền nợ gốc (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="amount" id="create_amount" class="form-control fw-bold fs-6 rounded-start-3 shadow-none currency-input" placeholder="0" required>
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                        </div>

                        <!-- Đã thanh toán trước -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Đã thanh toán trước (nếu có)
                            </label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="paid_amount" class="form-control rounded-start-3 shadow-none currency-input" placeholder="0" value="0">
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                            <div class="form-text small">Nhập 0 nếu chưa thanh toán đợt nào</div>
                        </div>

                        <!-- Ngày phát sinh -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Ngày phát sinh <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="issue_date" class="form-control rounded-3 shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Hạn thanh toán -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Hạn thanh toán (Ngày hẹn trả)
                            </label>
                            <input type="date" name="due_date" class="form-control rounded-3 shadow-none">
                            <div class="form-text small">Hệ thống sẽ tự động nhắc nhở khi đến hạn</div>
                        </div>

                        <!-- Ghi chú thêm -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú &amp; thỏa thuận thanh toán</label>
                            <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ví dụ: Thanh toán làm 2 đợt sau nghiệm thu, số tài khoản nhận tiền..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Khoản Nợ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: GHI NHẬN THANH TOÁN -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="navbar-brand-badge" style="width: 2rem; height: 2rem; font-size: 1rem; background-color: #ecfdf5; color: #047857;">
                        <i class="bi bi-credit-card-2-front"></i>
                    </span>
                    <span id="paymentModalTitle">Ghi Nhận Thanh Toán</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Thông tin tóm tắt khoản nợ -->
                    <div class="p-3 mb-3 rounded-3" style="background-color: var(--blue-soft-50); border: 0.0625rem solid var(--blue-soft-200);">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span id="paymentDebtCode" class="fw-bold font-monospace text-dark"></span>
                            <span id="paymentDebtPartner"></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="text-muted small">Số tiền còn nợ:</span>
                            <span id="paymentDebtRemaining" class="fs-5 fw-bold text-primary"></span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Số tiền thanh toán (VNĐ) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" inputmode="numeric" name="amount" id="paymentInputAmount" class="form-control fs-5 fw-bold text-success rounded-start-3 shadow-none currency-input" required>
                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Ngày thanh toán <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="payment_date" class="form-control rounded-3 shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Hình thức thanh toán <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" class="form-select rounded-3 shadow-none" required>
                                <option value="transfer">Chuyển khoản ngân hàng</option>
                                <option value="cash">Tiền mặt</option>
                                <option value="other">Hình thức khác</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Mã tham chiếu / Giao dịch ngân hàng</label>
                            <input type="text" name="reference_code" class="form-control rounded-3 shadow-none" placeholder="Mã lệnh chuyển khoản, số phiếu thu/chi...">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú thanh toán</label>
                            <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ghi chú đợt thanh toán này..."></textarea>
                        </div>
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
<!-- MODAL 3: XEM LỊCH SỬ THANH TOÁN -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Lịch Sử Thanh Toán</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="historyContent" class="p-3">
                    <!-- Dữ liệu JS sẽ đổ vào đây -->
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: SỬA KHOẢN CÔNG NỢ -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditDebt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Chỉnh Sửa Chứng Từ <span id="editDebtCode" class="font-monospace text-primary"></span></span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDebtForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Loại công nợ</label>
                            <select name="type" id="edit_type" class="form-select rounded-3 shadow-none" required>
                                <option value="receivable">📥 Công Nợ Phải Thu</option>
                                <option value="payable">📤 Công Nợ Phải Trả</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Đơn vị / Công ty quản lý</label>
                            <select name="company_id" id="edit_company_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Đối tác / Khách hàng</label>
                            <select name="partner_id" id="edit_partner_id" class="form-select rounded-3 shadow-none" required>
                                @foreach($partners as $p)
                                    <option value="{{ $p->id }}" data-company-id="{{ $p->company_id }}">{{ $p->name }} {{ $p->phone ? '('.$p->phone.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số tiền nợ gốc (VNĐ)</label>
                            <input type="text" inputmode="numeric" name="amount" id="edit_amount" class="form-control fw-bold rounded-3 shadow-none currency-input" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Nội dung diễn giải</label>
                            <input type="text" name="title" id="edit_title" class="form-control rounded-3 shadow-none" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Ngày phát sinh</label>
                            <input type="date" name="issue_date" id="edit_issue_date" class="form-control rounded-3 shadow-none" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Hạn thanh toán</label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control rounded-3 shadow-none">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Ghi chú</label>
                            <textarea name="notes" id="edit_notes" class="form-control rounded-3 shadow-none" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: THÊM CÔNG TY MỚI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCompany" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-building-add text-primary"></i>
                    <span>Thêm Doanh Nghiệp / Công Ty Mới</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('company.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Tên doanh nghiệp / Công ty <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Công ty TNHH Cát Vượng, Công ty Nam Phát..." required>
                    </div>
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
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Người đại diện</label>
                            <input type="text" name="representative" class="form-control rounded-3 shadow-none" placeholder="Họ và tên người đại diện...">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Chức vụ</label>
                            <input type="text" name="position" class="form-control rounded-3 shadow-none" placeholder="VD: Giám Đốc, TGĐ...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control rounded-3 shadow-none" placeholder="Số hotline/kế toán...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Địa chỉ văn phòng</label>
                        <input type="text" name="address" class="form-control rounded-3 shadow-none" placeholder="Địa chỉ trụ sở...">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Ghi chú</label>
                        <textarea name="notes" class="form-control rounded-3 shadow-none" rows="2" placeholder="Ghi chú về tài khoản, thông tin thêm..."></textarea>
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
<!-- MODAL 6: THÊM ĐỐI TÁC MỚI -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPartner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus text-primary"></i>
                    <span>Thêm Khách Hàng / Đối Tác</span>
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('partner.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Tên khách hàng / Nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3 shadow-none" placeholder="Ví dụ: Công ty Xây Dựng Á Đông, Anh Hùng..." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Phân loại</label>
                            <select name="type" class="form-select rounded-3 shadow-none">
                                <option value="customer">Khách hàng (Phải thu)</option>
                                <option value="supplier">Nhà cung cấp (Phải trả)</option>
                                <option value="both">Cả hai chiều</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Thuộc công ty nào</label>
                            <select name="company_id" class="form-select rounded-3 shadow-none">
                                <option value="">Dùng chung tất cả</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-3 shadow-none" placeholder="09xx...">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 shadow-none" placeholder="email@gmail.com">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small mb-1">Địa chỉ / Ghi chú</label>
                        <input type="text" name="address" class="form-control rounded-3 shadow-none" placeholder="Địa chỉ đối tác...">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-theme-primary rounded-pill px-4">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Đối Tác
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function formatNumberWithCommas(value) {
        if (value === null || value === undefined || value === '') return '';
        let clean = value.toString().replace(/[^0-9]/g, '');
        if (!clean) return '';
        return clean.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function parseNumberFromCommas(value) {
        if (!value) return 0;
        const clean = value.toString().replace(/,/g, '');
        return parseFloat(clean) || 0;
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('currency-input')) {
            const rawVal = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = formatNumberWithCommas(rawVal);
        }
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            form.querySelectorAll('.currency-input').forEach(input => {
                input.value = input.value.replace(/,/g, '');
            });
        });
    });

    function setupPartnerFilter(companySelectNameOrId, partnerSelectId) {
        const compSelect = document.querySelector(`select[name="${companySelectNameOrId}"]`) || document.getElementById(companySelectNameOrId);
        const partnerSelect = document.getElementById(partnerSelectId);
        if (!partnerSelect) return;

        function filterOptions() {
            const selectedCompId = compSelect ? compSelect.value : '';
            const options = partnerSelect.querySelectorAll('option');
            let hasValidSelected = false;

            options.forEach(opt => {
                const compId = opt.getAttribute('data-company-id');
                if (!compId || opt.value === '' || opt.value === 'NEW') {
                    opt.hidden = false;
                    opt.disabled = false;
                } else if (!selectedCompId || compId == selectedCompId) {
                    opt.hidden = false;
                    opt.disabled = false;
                    if (opt.selected) hasValidSelected = true;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                    if (opt.selected) opt.selected = false;
                }
            });

            if (!hasValidSelected && partnerSelect.value !== 'NEW' && partnerSelect.value !== '') {
                partnerSelect.value = '';
            }
        }

        if (compSelect) {
            compSelect.addEventListener('change', filterOptions);
            filterOptions();
        }
    }

    setupPartnerFilter('company_id', 'create_partner_select');
    setupPartnerFilter('edit_company_id', 'edit_partner_id');

    // Dữ liệu debts từ backend cho client JS
    const debtsData = @json($debts->items());

    // Toggle nhập nhanh đối tác mới khi chọn select
    const partnerSelect = document.getElementById('create_partner_select');
    const quickBox = document.getElementById('quickNewPartnerBox');
    if (partnerSelect && quickBox) {
        partnerSelect.addEventListener('change', function() {
            if (this.value === 'NEW') {
                quickBox.classList.remove('d-none');
                quickBox.querySelector('input[name="new_partner_name"]').focus();
            } else {
                quickBox.classList.add('d-none');
            }
        });
    }

    // Mở modal thanh toán (đã đổi tên thành Thanh Toán)
    function openPaymentModal(debt) {
        const modal = new bootstrap.Modal(document.getElementById('modalPayment'));
        const form = document.getElementById('paymentForm');
        form.action = `/debts/${debt.id}/payments`;

        const isReceivable = debt.type === 'receivable';
        document.getElementById('paymentModalTitle').textContent = isReceivable ? 'Ghi Nhận Thanh Toán (Thu từ khách)' : 'Ghi Nhận Thanh Toán (Trả đối tác)';
        document.getElementById('paymentDebtCode').textContent = debt.code;
        document.getElementById('paymentDebtPartner').textContent = debt.partner ? debt.partner.name : '';
        
        const remaining = Math.max(0, parseFloat(debt.amount) - parseFloat(debt.paid_amount));
        document.getElementById('paymentDebtRemaining').textContent = new Intl.NumberFormat('vi-VN').format(remaining) + ' đ';
        document.getElementById('paymentInputAmount').value = formatNumberWithCommas(remaining);

        modal.show();
    }

    // Mở modal chỉnh sửa khoản nợ
    function openEditModal(debt) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditDebt'));
        const form = document.getElementById('editDebtForm');
        form.action = `/debts/${debt.id}`;

        document.getElementById('editDebtCode').textContent = `[${debt.code}]`;
        document.getElementById('edit_type').value = debt.type;
        document.getElementById('edit_company_id').value = debt.company_id;
        const editCompSelect = document.getElementById('edit_company_id');
        if (editCompSelect) {
            editCompSelect.dispatchEvent(new Event('change'));
        }
        document.getElementById('edit_partner_id').value = debt.partner_id;
        document.getElementById('edit_title').value = debt.title;
        document.getElementById('edit_amount').value = formatNumberWithCommas(debt.amount);
        document.getElementById('edit_issue_date').value = debt.issue_date ? debt.issue_date.substring(0, 10) : '';
        document.getElementById('edit_due_date').value = debt.due_date ? debt.due_date.substring(0, 10) : '';
        document.getElementById('edit_notes').value = debt.notes || '';

        modal.show();
    }

    // Hiển thị lịch sử thanh toán
    function showPaymentHistory(debtId, debtCode) {
        const debt = debtsData.find(d => d.id == debtId);
        if (!debt) return;

        const container = document.getElementById('historyContent');
        if (!debt.payments || debt.payments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clock-history fs-2 opacity-50"></i>
                    <p class="mt-2 mb-0 small">Khoản nợ này chưa có lần thanh toán nào.</p>
                </div>
            `;
        } else {
            let html = `
                <div class="mb-2 fw-semibold text-dark small">Chứng từ: <span class="font-monospace text-primary">${debtCode}</span></div>
                <div class="list-group list-group-flush border rounded-3 overflow-hidden">
            `;
            debt.payments.forEach((p, idx) => {
                const dateStr = p.payment_date ? p.payment_date.substring(0, 10) : '';
                const amountFormatted = new Intl.NumberFormat('vi-VN').format(p.amount);
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div>
                            <div class="fw-bold text-success">+ ${amountFormatted} đ</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                Ngày: ${dateStr} • Hình thức: ${p.payment_method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt'}
                                ${p.reference_code ? `• Mã GD: ${p.reference_code}` : ''}
                            </div>
                            ${p.notes ? `<div class="text-secondary small fst-italic" style="font-size: 0.75rem;">"${p.notes}"</div>` : ''}
                        </div>
                        <form action="/payments/${p.id}" method="POST" onsubmit="return confirm('Bạn có muốn xóa lần thanh toán này không?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Xóa lần thanh toán này">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                `;
            });
            html += `</div>`;
            container.innerHTML = html;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalHistory'));
        modal.show();
    }
</script>
@endpush
@endsection
