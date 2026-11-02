<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Bản Đối Soát Công Nợ - {{ $debt->code }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* CSS tuân thủ nghiêm ngặt: KHÔNG DÙNG ĐƠN VỊ PX */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 2rem 0;
            font-size: 0.95rem;
        }

        .statement-sheet {
            background-color: #ffffff;
            max-width: 52rem;
            margin: 0 auto;
            padding: 3rem 3.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.05);
            border: 0.0625rem solid #e2e8f0;
        }

        .header-title {
            color: #0369a1;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .statement-table th {
            background-color: #f0f7ff;
            color: #0369a1;
            border-bottom: 0.0625rem solid #bae6fd;
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 0.75rem 1rem;
        }

        .statement-table td {
            padding: 0.75rem 1rem;
            border-bottom: 0.0625rem solid #f1f5f9;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .statement-sheet {
                box-shadow: none;
                border: none;
                padding: 1.5rem;
                max-width: 100%;
            }
            .d-print-none {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar điều khiển khi xem trên web -->
    <div class="container d-print-none text-center mb-3" style="max-width: 52rem;">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm border">
            <a href="{{ route('debt.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Quay lại Danh sách
            </a>
            <div class="fw-semibold text-dark">
                Mã chứng từ: <span class="font-monospace text-primary">{{ $debt->code }}</span>
            </div>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3" style="background-color: #0284c7; border-color: #0284c7;">
                <i class="bi bi-printer me-1"></i> In / Xuất PDF
            </button>
        </div>
    </div>

    <!-- Tờ biên bản đối soát -->
    <div class="statement-sheet">
        <!-- Tiêu đề công ty -->
        <div class="row align-items-center pb-4 mb-4 border-bottom">
            <div class="col-8">
                <h4 class="header-title mb-1">{{ $debt->company->name ?? 'ĐƠN VỊ QUẢN LÝ' }}</h4>
                @if($debt->company && $debt->company->phone)
                    <div class="text-muted small"><i class="bi bi-telephone"></i> Điện thoại: {{ $debt->company->phone }}</div>
                @endif
                @if($debt->company && $debt->company->address)
                    <div class="text-muted small"><i class="bi bi-geo-alt"></i> Địa chỉ: {{ $debt->company->address }}</div>
                @endif
            </div>
            <div class="col-4 text-end">
                <span class="badge {{ $debt->type === 'receivable' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle' }} px-3 py-2 fs-6">
                    {{ $debt->type === 'receivable' ? 'PHIẾU PHẢI THU' : 'PHIẾU PHẢI TRẢ' }}
                </span>
                <div class="text-muted small mt-1 font-monospace">{{ $debt->code }}</div>
            </div>
        </div>

        <div class="text-center my-4">
            <h3 class="fw-bold text-dark text-uppercase mb-1" style="letter-spacing: 0.05em;">BIÊN BẢN ĐỐI SOÁT CÔNG NỢ</h3>
            <div class="text-muted small">
                Ngày phát sinh: {{ \Carbon\Carbon::parse($debt->issue_date)->format('d/m/Y') }} 
                @if($debt->due_date)
                    • Hạn thanh toán: {{ \Carbon\Carbon::parse($debt->due_date)->format('d/m/Y') }}
                @endif
            </div>
        </div>

        <!-- Thông tin 2 bên đối soát -->
        <div class="row g-3 p-3 mb-4 rounded-3" style="background-color: #f8fafc; border: 0.0625rem solid #e2e8f0;">
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase">Đơn Vị Quản Lý:</div>
                <div class="fw-bold text-dark fs-6 mt-1">{{ $debt->company->name ?? '---' }}</div>
                <div class="text-muted small">Mã: {{ $debt->company->code ?? 'N/A' }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase">Bên Đối Tác / Khách Hàng:</div>
                <div class="fw-bold text-dark fs-6 mt-1">{{ $debt->partner->name ?? '---' }}</div>
                @if($debt->partner && $debt->partner->phone)
                    <div class="text-muted small">Điện thoại: {{ $debt->partner->phone }}</div>
                @endif
                @if($debt->partner && $debt->partner->address)
                    <div class="text-muted small">Địa chỉ: {{ $debt->partner->address }}</div>
                @endif
            </div>
        </div>

        <!-- Nội dung công nợ -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-2">1. Nội dung khoản nợ</h6>
            <div class="p-3 rounded-3 border bg-white">
                <div class="fw-semibold text-dark">{{ $debt->title }}</div>
                @if($debt->notes)
                    <div class="text-muted small mt-1">Ghi chú: {{ $debt->notes }}</div>
                @endif
            </div>
        </div>

        <!-- Bảng chi tiết đối soát -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-2">2. Lịch sử thanh toán & Số dư</h6>
            <table class="table statement-table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width: 3rem;" class="text-center">STT</th>
                        <th>Nội dung thanh toán / Đợt</th>
                        <th style="width: 9rem;">Ngày GD</th>
                        <th style="width: 8rem;">Hình thức</th>
                        <th class="text-end" style="width: 10rem;">Số tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center fw-bold">#</td>
                        <td class="fw-bold text-dark">Số tiền nợ gốc ban đầu</td>
                        <td>{{ \Carbon\Carbon::parse($debt->issue_date)->format('d/m/Y') }}</td>
                        <td>Hợp đồng / HĐ</td>
                        <td class="text-end fw-bold text-dark">{{ number_format($debt->amount, 0, ',', '.') }} đ</td>
                    </tr>
                    @forelse($debt->payments as $index => $payment)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                Thanh toán đợt {{ $index + 1 }}
                                @if($payment->reference_code)
                                    <span class="text-muted small">(Mã GD: {{ $payment->reference_code }})</span>
                                @endif
                                @if($payment->notes)
                                    <div class="text-secondary small fst-italic">"{{ $payment->notes }}"</div>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                            <td>{{ $payment->payment_method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt' }}</td>
                            <td class="text-end text-success fw-medium">- {{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted small py-2 fst-italic">
                                Chưa ghi nhận đợt thanh toán nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-semibold">Tổng số tiền đã thanh toán:</td>
                        <td class="text-end fw-bold text-success">{{ number_format($debt->paid_amount, 0, ',', '.') }} đ</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold fs-6">SỐ DƯ CÒN LẠI PHẢI {{ $debt->type === 'receivable' ? 'THU' : 'TRẢ' }}:</td>
                        <td class="text-end fw-bold fs-6 text-primary">{{ number_format($debt->remaining_amount, 0, ',', '.') }} đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Chữ ký 2 bên -->
        <div class="row text-center mt-5 pt-3">
            <div class="col-6">
                <div class="fw-bold text-dark text-uppercase">Đại Diện Bên Thu / Quản Lý</div>
                <div class="text-muted small fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
            <div class="col-6">
                <div class="fw-bold text-dark text-uppercase">Đại Diện Bên Đối Tác / Khách Hàng</div>
                <div class="text-muted small fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
        </div>
    </div>

</body>
</html>
