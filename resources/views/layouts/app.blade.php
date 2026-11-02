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
                            <i class="bi bi-buildings"></i>
                            <span>Doanh Nghiệp ({{ \App\Models\Company::count() }})</span>
                        </a>
                    </li>
                </ul>

                <!-- Nút hành động theo ngữ cảnh trang hiện tại -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if(Request::routeIs('company.*'))
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
                    @else
                        <a href="{{ route('invoice.in') }}" class="btn btn-theme-primary btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-receipt"></i>
                            <span>Vào Quản Lý Hóa Đơn</span>
                        </a>
                    @endif
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

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
