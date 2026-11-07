<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản Lý Hóa Đơn - Đầu Vào & Xuất Ra')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* CSS tuân thủ nghiêm ngặt: KHÔNG DÙNG ĐƠN VỊ PX, chỉ dùng rem, em, %, vh, vw */
        :root {
            --app-bg: #f3f7fc;
            --blue-soft-50: #f0f7ff;
            --blue-soft-100: #e0f2fe;
            --blue-soft-200: #bae6fd;
            --blue-primary: #0284c7;
            --blue-dark: #0369a1;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-soft: #e2e8f0;
            --radius-md: 0.65rem;
            --radius-lg: 0.95rem;
            --radius-xl: 1.15rem;
            --card-shadow: 0 0.125rem 0.375rem rgba(15, 23, 42, 0.04);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--app-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 0.92rem;
            line-height: 1.5;
        }

        /* Thanh tiêu đề mềm mại */
        .app-navbar {
            background-color: #ffffff;
            border-bottom: 0.0625rem solid var(--border-soft);
            box-shadow: 0 0.0625rem 0.25rem rgba(0, 0, 0, 0.03);
        }

        .navbar-brand-badge {
            background-color: var(--blue-soft-100);
            color: var(--blue-dark);
            width: 2.35rem;
            height: 2.35rem;
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Thẻ thống kê matte, border-radius mềm mại */
        .stat-card {
            background: #ffffff;
            border: 0.0625rem solid var(--border-soft);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .stat-card:hover {
            transform: translateY(-0.1rem);
            box-shadow: 0 0.375rem 0.75rem rgba(2, 132, 199, 0.08);
        }

        .stat-icon-wrapper {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* Bo góc mềm cho Form input & Select */
        .form-control, .form-select {
            border: 0.0625rem solid var(--border-soft);
            border-radius: var(--radius-md) !important;
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            transition: all 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--blue-primary);
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.15);
        }

        /* Bảng danh sách sạch sẽ */
        .table-custom {
            margin-bottom: 0;
        }

        .table-custom thead th {
            background-color: var(--blue-soft-50);
            color: var(--text-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.03em;
            padding: 0.85rem 1rem;
            border-bottom: 0.0625rem solid var(--border-soft);
            white-space: nowrap;
        }

        .table-custom tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            border-bottom: 0.0625rem solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badge mềm mại với border-radius đẹp */
        .badge-soft-info {
            background-color: var(--blue-soft-100);
            color: var(--blue-dark);
            font-weight: 600;
            border: 0.0625rem solid var(--blue-soft-200);
            border-radius: 0.5rem;
        }

        .badge-soft-success {
            background-color: #ecfdf5;
            color: #047857;
            font-weight: 600;
            border: 0.0625rem solid #a7f3d0;
            border-radius: 0.5rem;
        }

        .badge-soft-warning {
            background-color: #fffbeb;
            color: #b45309;
            font-weight: 600;
            border: 0.0625rem solid #fde68a;
            border-radius: 0.5rem;
        }

        .badge-soft-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            font-weight: 600;
            border: 0.0625rem solid #fecaca;
            border-radius: 0.5rem;
        }

        /* Nút màu xanh dịu chuẩn chỉnh */
        .btn-soft-primary {
            background-color: var(--blue-soft-100);
            color: var(--blue-dark);
            border: 0.0625rem solid var(--blue-soft-200);
            font-weight: 500;
            border-radius: var(--radius-md);
        }

        .btn-soft-primary:hover, .btn-soft-primary:focus {
            background-color: var(--blue-primary);
            color: #ffffff;
            border-color: var(--blue-primary);
        }

        .btn-theme-primary {
            background-color: var(--blue-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            border-radius: var(--radius-md);
            transition: all 0.15s ease;
        }

        .btn-theme-primary:hover, .btn-theme-primary:focus {
            background-color: var(--blue-dark);
            color: #ffffff;
        }

        .company-detail-link {
            transition: color 0.15s ease, text-decoration 0.15s ease;
        }
        .company-detail-link:hover {
            color: var(--blue-primary) !important;
            text-decoration: underline !important;
            cursor: pointer;
        }

        /* In ấn */
        @media print {
            .app-navbar, .d-print-none, .btn, .modal-backdrop {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg app-navbar sticky-top py-2 d-print-none">
        <div class="container-fluid px-3 px-md-4">
            <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ route('invoice.in') }}">
                <span class="navbar-brand-badge">
                    <i class="bi bi-receipt-cutoff"></i>
                </span>
                <div>
                    <div class="fw-bold fs-6 text-dark mb-0" style="letter-spacing: -0.01em;">QUẢN LÝ HÓA ĐƠN</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Hóa Đơn Đầu Vào &amp; Hóa Đơn Xuất Ra • Đa Doanh Nghiệp</div>
                </div>
            </a>

            <!-- Nút toggle mobile -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#appNav" aria-controls="appNav" aria-expanded="false">
                <i class="bi bi-list fs-3 text-secondary"></i>
            </button>

            <!-- Menu điều hướng: Hóa Đơn Đầu Vào & Hóa Đơn Xuất Ra -->
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="appNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-pill fw-medium d-flex align-items-center gap-1 {{ Request::routeIs('invoice.in') ? 'active bg-primary-subtle text-primary fw-bold' : 'text-secondary' }}" href="{{ route('invoice.in') }}">
                            <i class="bi bi-box-arrow-in-down-left"></i>
                            <span>Hóa Đơn Đầu Vào</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-pill fw-medium d-flex align-items-center gap-1 {{ Request::routeIs('invoice.out') ? 'active bg-primary-subtle text-primary fw-bold' : 'text-secondary' }}" href="{{ route('invoice.out') }}">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Hóa Đơn Xuất Ra</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-pill fw-medium d-flex align-items-center gap-1 {{ Request::routeIs('company.*') ? 'active bg-primary-subtle text-primary fw-bold' : 'text-secondary' }}" href="{{ route('company.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Doanh Nghiệp ({{ (Auth::check() && !Auth::user()->isAdmin() && Auth::user()->company_id) ? \App\Models\Partner::where('company_id', Auth::user()->company_id)->count() : \App\Models\Partner::count() }})</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 py-2 rounded-pill fw-medium d-flex align-items-center gap-1 {{ Request::routeIs('dashboard.*') ? 'active bg-primary-subtle text-primary fw-bold' : 'text-secondary' }}" href="{{ route('dashboard.index') }}">
                            <i class="bi bi-pie-chart-fill text-primary"></i>
                            <span>Sơ Đồ Phân Tích</span>
                        </a>
                    </li>
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 rounded-pill fw-medium d-flex align-items-center gap-1 {{ Request::routeIs('my-company.*') ? 'active bg-primary-subtle text-primary fw-bold' : 'text-secondary' }}" href="{{ route('my-company.index') }}">
                                <i class="bi bi-buildings-fill text-primary"></i>
                                <span>Quản Lý CTY ({{ \App\Models\Company::count() }})</span>
                            </a>
                        </li>
                    @endif
                </ul>

                <!-- Nút hành động theo ngữ cảnh trang hiện tại -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if(Request::routeIs('my-company.*'))
                        <button type="button" class="btn btn-theme-primary btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddMyCompany">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>+ Thêm CTY Mới</span>
                        </button>
                    @elseif(Request::routeIs('company.*'))
                        <button type="button" class="btn btn-theme-primary btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddCompany">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>+ Thêm Doanh Nghiệp Mới</span>
                        </button>
                    @elseif(Request::routeIs('invoice.in'))
                        <button type="button" class="btn btn-warning text-dark fw-semibold btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm" style="background-color: #fef3c7; border: 0.0625rem solid #fde68a;" data-bs-toggle="modal" data-bs-target="#modalCreateInvoiceFlow">
                            <i class="bi bi-plus-circle-fill" style="color: #d97706 !important;"></i>
                            <span>+ Thêm Hóa Đơn Đầu Vào</span>
                        </button>
                    @elseif(Request::routeIs('invoice.out'))
                        <button type="button" class="btn btn-theme-primary btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateInvoiceFlow">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>+ Thêm Hóa Đơn Xuất Ra</span>
                        </button>
                    @elseif(Request::routeIs('dashboard.*'))
                        <button type="button" class="btn btn-outline-secondary bg-white btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm" onclick="window.print()" title="In hoặc Lưu PDF Báo Cáo Phân Tích">
                            <i class="bi bi-printer"></i>
                            <span>In Báo Cáo</span>
                        </button>
                    @else
                        <a href="{{ route('invoice.in') }}" class="btn btn-theme-primary btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-receipt"></i>
                            <span>Vào Quản Lý Hóa Đơn</span>
                        </a>
                    @endif

                    @auth
                        <div class="dropdown ms-lg-2">
                            <button class="btn btn-light btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-2 border shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center" style="width: 1.65rem; height: 1.65rem; font-size: 0.75rem;">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <span class="fw-semibold text-dark small d-none d-sm-inline">{{ Auth::user()->display_name }}</span>
                                <i class="bi bi-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2 mt-1" style="min-width: 15rem;">
                                <li class="px-3 py-2 border-bottom mb-1">
                                    <div class="fw-bold text-dark small d-flex align-items-center justify-content-between gap-2">
                                        <span>{{ Auth::user()->display_name }}</span>
                                        @if(Auth::user()->isAdmin())
                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 0.65rem;">Admin</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">CTY</span>
                                        @endif
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Tài khoản: <strong>{{ Auth::user()->name }}</strong></div>
                                </li>
                                @if(Auth::user()->isAdmin())
                                    <li>
                                        <button type="button" class="dropdown-item small py-2 rounded-3 d-flex align-items-center gap-2 text-dark" data-bs-toggle="modal" data-bs-target="#modalManageUsers">
                                            <i class="bi bi-people-fill text-primary"></i>
                                            <span>Quản lý tài khoản CTY</span>
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                @endif
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item small text-danger py-2 rounded-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Thông báo Alert -->
    <div class="container-fluid px-3 px-md-4 pt-3 d-print-none">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm d-flex align-items-center mb-3" style="background-color: #ecfdf5; color: #065f46;" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm d-flex align-items-center mb-3" style="background-color: #fef2f2; color: #991b1b;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-3" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Có thông tin chưa hợp lệ:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-4 text-center text-muted d-print-none" style="font-size: 0.8rem; border-color: var(--border-soft) !important;">
        <div class="container-fluid px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div>
                <strong>Hệ Thống Quản Lý Hóa Đơn</strong> • Quản lý linh hoạt đa doanh nghiệp
            </div>
            <div>
                Hóa Đơn Đầu Vào &amp; Hóa Đơn Xuất Ra
            </div>
        </div>
    </footer>

    @auth
        @if(Auth::user()->isAdmin())
            @php
                $systemCompanies = \App\Models\Company::orderBy('name')->get();
                $systemUsers = \App\Models\User::with('company')->orderBy('id')->get();
            @endphp
            <!-- Modal Quản Lý Tài Khoản CTY -->
            <div class="modal fade" id="modalManageUsers" tabindex="-1" aria-labelledby="modalManageUsersLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-header border-bottom py-3 px-4" style="background-color: var(--blue-soft-50);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 2rem; height: 2rem; font-size: 0.9rem;">
                                    <i class="bi bi-shield-lock-fill"></i>
                                </span>
                                <div>
                                    <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="modalManageUsersLabel">Quản Lý Tài Khoản CTY</h5>
                                    <div class="text-muted" style="font-size: 0.75rem;">Tạo và phân quyền tài khoản cho từng công ty</div>
                                </div>
                            </div>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Card Tạo Tài Khoản -->
                            <div class="card border rounded-3 mb-4 shadow-none" style="background-color: #f8fafc; border-color: var(--border-soft) !important;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-person-plus-fill text-primary"></i>
                                        <span>Tạo tài khoản mới cho CTY</span>
                                    </h6>
                                    <form action="{{ route('users.store') }}" method="POST">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-dark mb-1">
                                                    Tài khoản <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="name" class="form-control form-control-sm rounded-3" placeholder="Ví dụ: cty_catvuong, cty_minhduc..." required autocomplete="off">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-dark mb-1">
                                                    Tên CTY <span class="text-danger">*</span>
                                                </label>
                                                <select name="company_id" class="form-select form-select-sm rounded-3" required>
                                                    <option value="" disabled selected>-- Chọn công ty gán vào tài khoản --</option>
                                                    @foreach($systemCompanies as $c)
                                                        <option value="{{ $c->id }}">
                                                            {{ $c->name }} ({{ $c->code ?: 'Chưa có MST' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-dark mb-1">
                                                    Mật khẩu <span class="text-danger">*</span>
                                                </label>
                                                <input type="password" name="password" class="form-control form-control-sm rounded-3" placeholder="Nhập mật khẩu (tối thiểu 3 ký tự)..." required autocomplete="new-password">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-dark mb-1">
                                                    Xác nhận mật khẩu <span class="text-danger">*</span>
                                                </label>
                                                <input type="password" name="password_confirmation" class="form-control form-control-sm rounded-3" placeholder="Nhập lại mật khẩu..." required autocomplete="new-password">
                                            </div>
                                        </div>
                                        <div class="mt-3 text-end">
                                            <button type="submit" class="btn btn-theme-primary btn-sm rounded-pill px-3 py-1 shadow-sm">
                                                <i class="bi bi-plus-circle me-1"></i> + Tạo Tài Khoản CTY
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Danh sách tài khoản hiện có -->
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-list-check text-primary"></i>
                                <span>Danh sách tài khoản hệ thống ({{ $systemUsers->count() }})</span>
                            </h6>
                            <div class="table-responsive rounded-3 border">
                                <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tài khoản</th>
                                            <th>Vai trò / CTY</th>
                                            <th>Ngày tạo</th>
                                            <th class="text-end">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($systemUsers as $u)
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-dark">{{ $u->name }}</span>
                                                    @if($u->id === Auth::id())
                                                        <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 0.65rem;">Hiện tại</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($u->isAdmin())
                                                        <span class="badge bg-danger-subtle text-danger fw-semibold px-2 py-1 rounded-pill">Quản trị viên</span>
                                                    @else
                                                        <span class="fw-semibold text-primary">{{ $u->company ? $u->company->name : 'Chưa gắn CTY' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted" style="font-size: 0.78rem;">
                                                    {{ $u->created_at ? $u->created_at->format('d/m/Y H:i') : '---' }}
                                                </td>
                                                <td class="text-end">
                                                    @if(!$u->isAdmin() && $u->id !== Auth::id())
                                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản [{{ $u->name }}] không?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" style="font-size: 0.75rem;" title="Xóa tài khoản">
                                                                <i class="bi bi-trash"></i> Xóa
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small" style="font-size: 0.75rem;">Mặc định</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Chưa có tài khoản nào.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
