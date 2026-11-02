<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn - {{ $invoice->invoice_number }}</title>
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

        .invoice-sheet {
            background-color: #ffffff;
            max-width: 52rem;
            margin: 0 auto;
            padding: 3rem 3.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.05);
            border: 0.0625rem solid #e2e8f0;
        }

        .header-title {
            color: #0369a1;
            font-weight: 800;
        }

        .invoice-table th {
            background-color: #f0f7ff;
            color: #0369a1;
            border-bottom: 0.0625rem solid #bae6fd;
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 0.75rem 1rem;
        }

        .invoice-table td {
            padding: 0.85rem 1rem;
            border-bottom: 0.0625rem solid #f1f5f9;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .invoice-sheet {
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
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm border">
            <a href="{{ $invoice->type === 'in' ? route('invoice.in') : route('invoice.out') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Quay lại Danh Sách Hóa Đơn
            </a>
            <div class="fw-semibold text-dark">
                Số HĐ: <span class="font-monospace text-primary">{{ $invoice->invoice_number }}</span>
            </div>
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3" style="background-color: #0284c7; border-color: #0284c7;">
                <i class="bi bi-printer me-1"></i> In / Xuất PDF
            </button>
        </div>
    </div>

    <!-- Tờ Hóa Đơn -->
    <div class="invoice-sheet">
        <!-- Header đơn vị -->
        <div class="row align-items-center pb-4 mb-4 border-bottom">
            <div class="col-7">
                <h4 class="header-title mb-1">{{ $invoice->company->name ?? 'ĐƠN VỊ KINH DOANH' }}</h4>
                @if($invoice->company && $invoice->company->phone)
                    <div class="text-muted small"><i class="bi bi-telephone"></i> Điện thoại: {{ $invoice->company->phone }}</div>
                @endif
                @if($invoice->company && $invoice->company->address)
                    <div class="text-muted small"><i class="bi bi-geo-alt"></i> Địa chỉ: {{ $invoice->company->address }}</div>
                @endif
            </div>
            <div class="col-5 text-end">
                <span class="badge {{ $invoice->type === 'out' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle' }} px-3 py-2 fs-6 rounded-pill">
                    {{ $invoice->type === 'out' ? 'HÓA ĐƠN BÁN HÀNG (ĐẦU RA)' : 'HÓA ĐƠN MUA HÀNG (ĐẦU VÀO)' }}
                </span>
                <div class="text-dark fw-bold font-monospace mt-2 fs-5">{{ $invoice->invoice_number }}</div>
                <div class="text-muted small">Ngày: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="text-center my-4">
            <h3 class="fw-bold text-dark text-uppercase mb-1" style="letter-spacing: 0.05em;">HÓA ĐƠN TÀI CHÍNH</h3>
            <div class="text-muted small">
                Trạng thái: 
                <span class="fw-bold {{ $invoice->status === 'paid' ? 'text-success' : 'text-danger' }}">
                    {{ $invoice->status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' }}
                </span>
                @if($invoice->due_date)
                    • Hạn trả: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                @endif
            </div>
        </div>

        <!-- Thông tin bên mua & bên bán -->
        <div class="row g-3 p-3 mb-4 rounded-3" style="background-color: #f8fafc; border: 0.0625rem solid #e2e8f0;">
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase">
                    {{ $invoice->type === 'out' ? 'Đơn Vị Xuất Hóa Đơn (Bên Bán):' : 'Đơn Vị Nhận Hóa Đơn (Bên Mua):' }}
                </div>
                <div class="fw-bold text-dark fs-6 mt-1">{{ $invoice->company->name ?? '---' }}</div>
                @if($invoice->company && $invoice->company->code)
                    <div class="text-muted small">Mã đơn vị: {{ $invoice->company->code }}</div>
                @endif
            </div>
            <div class="col-6">
                <div class="text-muted small fw-semibold text-uppercase">
                    {{ $invoice->type === 'out' ? 'Khách Hàng (Bên Mua):' : 'Nhà Cung Cấp (Bên Bán):' }}
                </div>
                <div class="fw-bold text-dark fs-6 mt-1">{{ $invoice->partner->name ?? '---' }}</div>
                @if($invoice->partner && $invoice->partner->phone)
                    <div class="text-muted small">Điện thoại: {{ $invoice->partner->phone }}</div>
                @endif
                @if($invoice->partner && $invoice->partner->address)
                    <div class="text-muted small">Địa chỉ: {{ $invoice->partner->address }}</div>
                @endif
            </div>
        </div>

        <!-- Bảng chi tiết hóa đơn -->
        <div class="mb-4">
            <table class="table invoice-table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width: 3rem;" class="text-center">STT</th>
                        <th>Nội Dung Hàng Hóa / Dịch Vụ</th>
                        <th style="width: 7rem;" class="text-center">Thuế VAT</th>
                        <th class="text-end" style="width: 12rem;">Thành Tiền (Trước thuế)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $invoice->title }}</div>
                            @if($invoice->notes)
                                <div class="text-muted small mt-1">Ghi chú: {{ $invoice->notes }}</div>
                            @endif
                        </td>
                        <td class="text-center fw-medium">{{ (float)$invoice->tax_rate }}%</td>
                        <td class="text-end fw-semibold">{{ number_format($invoice->subtotal, 0, ',', '.') }} đ</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-medium">Tiền trước thuế:</td>
                        <td class="text-end fw-semibold">{{ number_format($invoice->subtotal, 0, ',', '.') }} đ</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-medium">Tiền thuế GTGT ({{ (float)$invoice->tax_rate }}%):</td>
                        <td class="text-end fw-semibold text-dark">+{{ number_format($invoice->tax_amount, 0, ',', '.') }} đ</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold fs-6">TỔNG CỘNG THANH TOÁN:</td>
                        <td class="text-end fw-bold fs-5 text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Chữ ký 2 bên -->
        <div class="row text-center mt-5 pt-3">
            <div class="col-6">
                <div class="fw-bold text-dark text-uppercase">Người Lập Phiếu / Kế Toán</div>
                <div class="text-muted small fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
            <div class="col-6">
                <div class="fw-bold text-dark text-uppercase">Đại Diện Khách Hàng / Đối Tác</div>
                <div class="text-muted small fst-italic mb-5">(Ký, ghi rõ họ tên)</div>
            </div>
        </div>
    </div>

</body>
</html>
