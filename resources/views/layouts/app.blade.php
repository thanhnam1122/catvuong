<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cát Vượng - Hệ Thống Quản Lý & Báo Giá Sửa Chữa')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* CSS chuẩn Bootstrap, KHÔNG dùng đơn vị px */
        :root {
            --sidebar-width: 16rem;
            --main-bg: #f4f6f9;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --brand-primary: #0284c7;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', sans-serif;
            background-color: var(--main-bg);
            color: #0f172a;
            min-height: 100vh;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Left Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: #f8fafc;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 0.2rem 0 0.8rem rgba(0,0,0,0.15);
            transition: all 0.25s ease-in-out;
            z-index: 1040;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.2rem 1.25rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 0.08rem solid #334155;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            letter-spacing: 0.03em;
        }

        .sidebar-brand span {
            color: #38bdf8;
        }

        .sidebar-nav {
            padding: 1rem 0.6rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 0.4rem;
            font-size: 0.92rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .nav-item-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .nav-item-link.active {
            color: #ffffff;
            background-color: var(--brand-primary);
            font-weight: 600;
            box-shadow: 0 0.2rem 0.5rem rgba(2, 132, 199, 0.4);
        }

        .sidebar-footer {
            padding: 1rem 1.2rem;
            border-top: 0.08rem solid #334155;
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Main Content Wrapper */
        .main-content {
            flex-grow: 1;
            padding: 1.5rem 2rem;
            overflow-x: hidden;
        }

        /* Print Media CSS - Ẩn hoàn toàn Sidebar khi in ấn */
        @media print {
            .sidebar,
            .d-print-none {
                display: none !important;
            }

            .main-content {
                padding: 0 !important;
                margin: 0 !important;
            }

            .dashboard-wrapper {
                display: block !important;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar d-print-none">
            <div class="sidebar-brand">
                <i class="bi bi-tools fs-4 text-warning"></i>
                <div>CÁT VƯỢNG <span>ADMIN</span></div>
            </div>

            <nav class="sidebar-nav">
                <div class="text-uppercase text-secondary fs-7 fw-bold px-3 mb-1 mt-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Chức năng chính</div>

                <a href="{{ url('/bao-gia') }}" class="nav-item-link {{ Request::is('/') || Request::is('bao-gia*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text fs-5"></i>
                    <span>Phiếu Báo Giá Sửa Chữa</span>
                </a>

                <a href="{{ route('customers.index') }}" class="nav-item-link {{ Request::is('khach-hang*') ? 'active' : '' }}">
                    <i class="bi bi-people fs-5"></i>
                    <span>Quản Lý Khách Hàng</span>
                </a>

                <a href="{{ route('products.index') }}" class="nav-item-link {{ Request::is('san-pham*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam fs-5"></i>
                    <span>Sản Phẩm & Linh Kiện</span>
                </a>

                <a href="{{ route('tech-specs.index') }}" class="nav-item-link {{ Request::is('thong-so-trang-2*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-image fs-5"></i>
                    <span>Phụ Kiện Trang 2</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div>CÔNG TY TNHH CÁT VƯỢNG</div>
                <div class="text-secondary mt-1">Hệ thống quản lý v1.0</div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-print-none mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
