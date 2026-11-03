<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng Nhập Hệ Thống - Quản Lý Hóa Đơn</title>

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
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--app-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.92rem;
            line-height: 1.5;
            padding: 1.5rem 1rem;
        }

        .login-card {
            background: #ffffff;
            border: 0.0625rem solid var(--border-soft);
            border-radius: var(--radius-xl);
            box-shadow: 0 0.5rem 1.5rem rgba(15, 23, 42, 0.06);
            width: 100%;
            max-width: 27rem;
            overflow: hidden;
        }

        .navbar-brand-badge {
            background-color: var(--blue-soft-100);
            color: var(--blue-dark);
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            box-shadow: 0 0.25rem 0.75rem rgba(2, 132, 199, 0.15);
        }

        .form-control {
            border: 0.0625rem solid var(--border-soft);
            border-radius: var(--radius-md) !important;
            padding: 0.65rem 0.95rem;
            font-size: 0.92rem;
            transition: all 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--blue-primary);
            box-shadow: 0 0 0 0.2rem rgba(2, 132, 199, 0.15);
        }

        .input-group-text {
            border: 0.0625rem solid var(--border-soft);
            background-color: #f8fafc;
            color: var(--text-muted);
            border-radius: var(--radius-md) !important;
            font-size: 1rem;
        }

        .btn-theme-primary {
            background-color: var(--blue-primary);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: var(--radius-md);
            padding: 0.7rem 1.25rem;
            transition: all 0.15s ease;
        }

        .btn-theme-primary:hover, .btn-theme-primary:focus {
            background-color: var(--blue-dark);
            color: #ffffff;
            box-shadow: 0 0.25rem 0.75rem rgba(2, 132, 199, 0.25);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="p-4 p-md-5">
            <!-- Header biểu tượng & Tiêu đề -->
            <div class="text-center mb-4">
                <span class="navbar-brand-badge mb-3">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <h4 class="fw-bold text-dark mb-1">Đăng Nhập Hệ Thống</h4>
                <div class="text-muted small">
                    Quản Lý Hóa Đơn Đầu Vào, Xuất Ra &amp; Doanh Nghiệp
                </div>
            </div>

            <!-- Thông báo thành công / lỗi -->
            @if(session('success'))
                <div class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center mb-3" style="background-color: #ecfdf5; color: #065f46;" role="alert">
                    <i class="bi bi-check-circle-fill me-2 text-success"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-3 py-2 px-3 small mb-3" style="background-color: #fef2f2; color: #991b1b;" role="alert">
                    <div class="d-flex align-items-center mb-1 fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                        <span>Đăng nhập không thành công:</span>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Đăng Nhập -->
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <!-- Tài khoản -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small mb-1" for="loginEmail">
                        Tài khoản <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="email" id="loginEmail" class="form-control" placeholder="Nhập tài khoản..." value="{{ old('email') }}" required autofocus autocomplete="off">
                    </div>
                </div>

                <!-- Mật khẩu -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small mb-1" for="loginPassword">
                        Mật khẩu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Nhập mật khẩu..." required autocomplete="current-password">
                        <button class="btn btn-outline-secondary border-start-0 rounded-end-3" type="button" id="togglePasswordBtn" title="Hiện/ẩn mật khẩu">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Ghi nhớ đăng nhập -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                        <label class="form-check-label small text-muted" for="rememberMe">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                </div>

                <!-- Nút Đăng Nhập -->
                <button type="submit" class="btn btn-theme-primary w-100 rounded-pill d-flex align-items-center justify-content-center gap-2 shadow-sm mb-1">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span>Đăng Nhập Vào Hệ Thống</span>
                </button>
            </form>
        </div>

        <!-- Footer Card -->
        <div class="bg-light border-top py-3 px-4 text-center text-muted" style="font-size: 0.78rem;">
            Hệ Thống Quản Lý Hóa Đơn &amp; Doanh Nghiệp
        </div>
    </div>

    <script>
        // Toggle ẩn/hiện mật khẩu
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('loginPassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    </script>
</body>
</html>

