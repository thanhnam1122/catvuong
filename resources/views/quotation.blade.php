@extends('layouts.app')

@section('title', 'Phiếu Báo Giá Sửa Chữa - Công Ty TNHH Cát Vượng')

@section('content')
<style>
    /* CSS tuân thủ hoàn toàn quy định KHÔNG dùng đơn vị px (dùng rem, em, %, pt) */
    :root {
        --bs-font-sans-serif: 'Roboto', 'Times New Roman', 'Segoe UI', sans-serif;
        --main-font: 'Times New Roman', 'Roboto', serif;
        --brand-red: #c00000;
    }

    .paper-container {
        background: #ffffff;
        width: 100%;
        max-width: 52rem; /* ~ 832rem scaling */
        margin: 0 auto 2rem auto;
        padding: 2.2rem 2.5rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
        border-radius: 0.25rem;
        position: relative;
    }

    .page-break {
        page-break-before: always;
        margin-top: 3rem;
        padding-top: 1.5rem;
        border-top: 0.1rem dashed #cccccc;
    }

    .company-header-title {
        font-weight: 800;
        color: #000000;
        font-size: 1.35rem;
        letter-spacing: 0.02em;
    }

    .header-subtext {
        font-size: 0.82rem;
        line-height: 1.25;
        color: #222222;
    }

    .quote-main-title {
        font-size: 1.35rem;
        font-weight: bold;
        color: #000000;
        text-align: center;
        margin-top: 0.6rem;
        margin-bottom: 0.8rem;
    }

    .info-label {
        font-weight: 600;
        min-width: 7rem;
        display: inline-block;
    }

    /* Table styling khớp với bản gốc */
    .table-quote {
        border: 0.08rem solid #000000 !important;
        margin-bottom: 0.5rem;
        width: 100%;
    }

    .table-quote th, 
    .table-quote td {
        border: 0.08rem solid #000000 !important;
        padding: 0.3rem 0.4rem;
        vertical-align: middle;
        font-size: 0.83rem;
    }

    .table-quote th {
        background-color: #f2f2f2;
        text-align: center;
        font-weight: bold;
    }

    .terms-section {
        font-size: 0.82rem;
        line-height: 1.35;
    }

    .terms-title {
        font-weight: bold;
    }

    .signature-block {
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: bold;
        font-size: 0.88rem;
    }

    .signature-space {
        height: 4.5rem;
    }

    /* Form Editor controls styling */
    .form-control-sm-custom {
        font-size: 0.82rem;
        padding: 0.15rem 0.35rem;
        border-radius: 0.15rem;
        border: 0.06rem solid #ced4da;
    }

    .editor-only {
        display: block;
    }

    .floating-actions {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1050;
        background: #ffffff;
        padding: 0.6rem 1rem;
        border-radius: 2rem;
        box-shadow: 0 0.4rem 1.2rem rgba(0,0,0,0.2);
        border: 0.08rem solid #e2e8f0;
    }

    /* Autocomplete Suggestions Popup Styling */
    .autocomplete-wrapper {
        position: relative;
        width: 100%;
    }

    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 20rem;
        max-width: 32rem;
        max-height: 15rem;
        overflow-y: auto;
        background: #ffffff;
        border: 0.08rem solid #94a3b8;
        border-radius: 0.35rem;
        box-shadow: 0 0.6rem 1.5rem rgba(0,0,0,0.18);
        z-index: 1080;
        margin-top: 0.2rem;
        text-align: left;
    }

    .autocomplete-item {
        padding: 0.45rem 0.65rem;
        cursor: pointer;
        border-bottom: 0.05rem solid #f1f5f9;
        font-size: 0.82rem;
        line-height: 1.3;
        transition: background-color 0.15s ease;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    /* Print CSS A4 (Tuân thủ không dùng px) */
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 9.5pt !important;
        }

        .paper-container {
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .d-print-none,
        .editor-only,
        .floating-actions,
        .autocomplete-dropdown {
            display: none !important;
        }

        .page-break {
            page-break-before: always !important;
            margin-top: 0 !important;
            padding-top: 1.5rem !important;
            border-top: none !important;
        }

        .table-quote th, 
        .table-quote td {
            padding: 0.25rem 0.35rem !important;
            font-size: 9pt !important;
        }

        .signature-space {
            height: 4rem !important;
        }
    }
</style>

<!-- Floating Action Toolbar (Save/Print/Mode Controls) -->
<div class="floating-actions d-print-none d-flex align-items-center gap-2">
    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="window.print()">
        <i class="bi bi-printer-fill me-1"></i> In phiếu / Xuất PDF
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetToDefaultSample()">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Tải lại dữ liệu mẫu
    </button>
    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="toggleEditMode()" id="toggleEditBtn">
        <i class="bi bi-pencil-square me-1"></i> Khóa / Sửa Form
    </button>
</div>

<!-- Paper Container (A4 Printable Layout) -->
<div class="paper-container">
    <form id="quotationForm" onsubmit="return false;">
        
        <!-- ================= PAGE 1 ================= -->
        <!-- HEADER SECTION -->
        <div class="row align-items-start pb-2 border-bottom border-secondary-subtle">
            <!-- Center Company Name & Contact Info -->
            <div class="col">
                <div class="company-header-title text-uppercase">
                    CÔNG TY TNHH CÁT VƯỢNG/ <span style="font-family: 'SimSun', 'Microsoft YaHei', sans-serif;">吉 旺 責 任 有 限 公 司</span>
                </div>
                <div class="header-subtext mt-1">
                    <div><strong>辦公室/Văn Phòng :</strong> 12 Tô Ký, Phường Đông Hưng Thuận, TP. Hồ Chí Minh</div>
                    <div><strong>分支/Chi nhánh :</strong> 504/7 Nguyễn Văn Quá, Phường Đông Hưng Thuận, TP. Hồ Chí Minh</div>
                    <div><strong>稅號/Mã số thuế :</strong> 0306105289</div>
                    <div>
                        <strong>銷售部/ Kinh doanh:</strong> 0919679246 &nbsp;-&nbsp;
                        <strong>技術室/Kỹ Thuật:</strong> 0916344106 &nbsp;-&nbsp;
                        <strong>Teams:</strong> cancatvuong
                    </div>
                    <div>
                        <strong>Mail:</strong> ctycatvuong@gmail.com &nbsp;&nbsp;
                        <strong>Web:</strong> www.catvuong.com ; www.cancatvuong.com
                    </div>
                    <div class="fst-italic text-secondary" style="font-size: 0.78rem; margin-top: 0.15rem;">
                        Chuyên kinh doanh, sửa chữa các loại: Cân phân tích, Cân vàng, Cân thủy sản, Cân bàn, Cân ô tô, Cửa tự động . .v. .v.v. . . . .
                    </div>
                </div>
            </div>
        </div>

        <!-- TITLE & QUOTE METADATA -->
        <div class="row align-items-center my-2">
            <div class="col-8">
                <div class="quote-main-title text-center ms-5">
                    <span style="font-family: 'SimSun', sans-serif;">维修报价单</span>/PHIẾU BÁO GIÁ SỬA CHỮA
                </div>
            </div>
            <div class="col-4 header-subtext">
                <div class="d-flex justify-content-between">
                    <span><strong>报价单号/Số báo giá :</strong></span>
                    <input type="text" id="quoteNo" class="form-control-sm-custom fw-bold text-end border-0 p-0" style="width: 7rem;" value="2008097579">
                </div>
                <div class="d-flex justify-content-between">
                    <span><strong>报价日期/ngày báo giá:</strong></span>
                    <input type="text" id="quoteDate" class="form-control-sm-custom text-end border-0 p-0" style="width: 7rem;" value="2026/06/15">
                </div>
                <div class="d-flex justify-content-between">
                    <span><strong>负责业务/NV:</strong></span>
                    <input type="text" id="salesRep" class="form-control-sm-custom text-end border-0 p-0" style="width: 8rem;" value="Thuận 0919679246">
                </div>
                <div class="d-flex justify-content-between">
                    <span><strong>钱币/Tiền tệ:</strong></span>
                    <input type="text" id="currency" class="form-control-sm-custom text-end border-0 p-0 fw-bold" style="width: 4rem;" value="VNĐ">
                </div>
            </div>
        </div>

        <!-- CUSTOMER INFORMATION BLOCK (VỚI AUTOCOMPLETE TRỰC TIẾP KHI GÕ) -->
        <div class="header-subtext mb-2 p-2 rounded bg-light border border-secondary-subtle position-relative">
            <div class="row g-1">
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">客户名称/Kính gửi :</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="customerName" class="form-control form-control-sm border-0 bg-transparent fw-bold p-0" 
                               value="Công Ty Cổ Phần TuiCo 3" placeholder="Gõ tên công ty (vd: tuico3) để tìm..." 
                               autocomplete="off" oninput="searchCustomer(this.value)">
                        <div id="customerSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">联络人/Người liên hệ :</span>
                    <input type="text" id="contactPerson" class="form-control form-control-sm border-0 bg-transparent p-0 ms-1" value="Chị Hằng (thu mua) 0989 169170 , Chị Vân">
                </div>
                <div class="col-6 d-flex align-items-center">
                    <span class="info-label" style="min-width: 5rem;">电话/Tel :</span>
                    <input type="text" id="tel" class="form-control form-control-sm border-0 bg-transparent p-0 ms-1" value="02513 671222">
                </div>
                <div class="col-6 d-flex align-items-center">
                    <span class="info-label" style="min-width: 5rem;">传真/Fax :</span>
                    <input type="text" id="fax" class="form-control form-control-sm border-0 bg-transparent p-0 ms-1" value="02513 671666, 02513 671345">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">地址/Địa chỉ :</span>
                    <input type="text" id="address" class="form-control form-control-sm border-0 bg-transparent p-0 ms-1" value="Lô đất số 1-16, KCN Hố Nai, Phường Hố Nai, Thành Phố Đồng Nai, Việt Nam">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">Mail, Teams, Zalo :</span>
                    <input type="text" id="socialContact" class="form-control form-control-sm border-0 bg-transparent p-0 ms-1" value="Zalo, MinhHangCao, hang@tuico.com">
                </div>
            </div>
        </div>

        <!-- GREETING MESSAGE -->
        <div class="fst-italic header-subtext mb-2">
            <div>Xin chân thành cảm ơn Quý khách hàng đã quan tâm ủng hộ sản phẩm và dịch vụ của chúng tôi.</div>
            <div style="font-family: 'SimSun', sans-serif;">感謝貴客戶已關心使用本公司的產品!</div>
        </div>

        <!-- QUOTATION ITEMS TABLE -->
        <table class="table-quote table-sm" id="itemsTable">
            <thead>
                <tr>
                    <th style="width: 3.5rem;">STT</th>
                    <th style="width: 7.5rem;">Hãng SX<br><span style="font-family: 'SimSun', sans-serif;">品牌</span></th>
                    <th style="width: 8.5rem;">Model<br><span style="font-family: 'SimSun', sans-serif;">型號</span></th>
                    <th>Diễn giải<br><span style="font-family: 'SimSun', sans-serif;">說明</span></th>
                    <th style="width: 7.5rem;">Đơn giá<br><span style="font-family: 'SimSun', sans-serif;">單價</span></th>
                    <th style="width: 5.5rem;">Số lượng<br><span style="font-family: 'SimSun', sans-serif;">數量</span></th>
                    <th style="width: 8rem;">Thành tiền<br><span style="font-family: 'SimSun', sans-serif;">總金額</span></th>
                    <th class="editor-only d-print-none" style="width: 2.5rem;">Xóa</th>
                </tr>
            </thead>
            <tbody id="tableItemsBody">
                <!-- Rows rendered dynamically via JS -->
            </tbody>
            <tfoot>
                <!-- Subtotal Row -->
                <tr>
                    <td colspan="4" rowspan="3" class="align-top border-0">
                        <button type="button" class="btn btn-sm btn-outline-primary editor-only d-print-none mt-1" onclick="addNewRow()">
                            <i class="bi bi-plus-circle me-1"></i> Thêm dòng sản phẩm
                        </button>
                    </td>
                    <td colspan="2" class="fw-bold text-end">報價金額/Số tiền</td>
                    <td class="fw-bold text-end" id="subtotalCell">750.000</td>
                    <td class="editor-only d-print-none"></td>
                </tr>
                <!-- VAT Row -->
                <tr>
                    <td colspan="2" class="fw-bold text-end">
                        稅額/Thuế suất 
                        <input type="number" id="vatPercent" class="form-control-sm-custom d-inline-block text-center p-0" style="width: 2.5rem;" value="8" oninput="calculateTotals()"> %
                    </td>
                    <td class="fw-bold text-end" id="vatCell">60.000</td>
                    <td class="editor-only d-print-none"></td>
                </tr>
                <!-- Grand Total Row -->
                <tr>
                    <td colspan="2" class="fw-bold text-end text-uppercase" style="font-size: 0.88rem;">報價總額/Tổng cộng</td>
                    <td class="fw-bold text-end text-danger" style="font-size: 0.95rem;" id="grandTotalCell">810.000</td>
                    <td class="editor-only d-print-none"></td>
                </tr>
            </tfoot>
        </table>

        <!-- TERMS AND CONDITIONS (MỤC I - VII) -->
        <div class="terms-section mt-3">
            <div class="row g-1">
                <div class="col-12">
                    <span class="terms-title">I) Hình thức thanh toán:</span> Tiền mặt hoặc chuyển khoản.
                </div>
                <div class="col-12">
                    <span class="terms-title">II) Thời hạn giao hàng:</span> Từ 02 đến 03 ngày, kể từ ngày xác nhận hồi fax.
                </div>
                <div class="col-12">
                    <span class="terms-title">III) Thời gian bảo hành:</span> 01 tháng, kể từ ngày giao hàng/sửa chữa.
                </div>
                <div class="col-12">
                    <span class="terms-title">IV) Báo giá có giá trị trong 30 ngày</span>
                </div>
                <div class="col-12">
                    <span class="terms-title">V) Giá trên đã bao gồm phí vận chuyển thiết bị/sản phẩm đến khách hàng.</span>
                </div>
                <div class="col-12">
                    <span class="terms-title">VI) Giá trên chưa bao gồm phí kiểm định/ hiệu chuẩn của đơn vị đo lường nhà nước cấp.</span>
                </div>
                <div class="col-12 mt-1">
                    <span class="terms-title">VII) Các hạng mục thay thế và thời gian bảo hành:</span>
                    <div class="ps-3 mt-1">
                        <div><strong>1 .</strong> Hiệu chỉnh phần mềm, hư nguồn, vệ sinh cân : <strong>01 tháng</strong></div>
                        <div><strong>2 .</strong> Bình sạc, Jack LoadCell, Jack tín hiệu, Jack nguồn, nút nhấn, công tắc : <strong>03 tháng</strong> (bảo hành khi bình không bị phù)</div>
                        <div><strong>3 .</strong> Thay LoadCell (cảm biến lực), màn hình dislay, đầu đọc (đầu hiển thị số), board mạch, bàn phím : <strong>12 tháng</strong></div>
                        <div><strong>4 .</strong> Adaptor, cục sạc, dây nguồn, dĩa cân, cổ cân, chân đế cân : <strong>không thuộc phạm vi bảo hành</strong></div>
                    </div>
                </div>
                <div class="col-12 mt-1 fw-bold">
                    * Nhận bảo hành, bảo trì, sửa chữa tận nơi các loại cân điện tử với giá cả phải chăng.
                </div>
                <div class="col-12 mt-1 d-flex align-items-center">
                    <span class="fw-bold" style="min-width: 12rem;">客户提意见/Ý kiến khách hàng:</span>
                    <input type="text" class="form-control form-control-sm border-0 border-bottom bg-transparent p-0 ms-1" placeholder="......................................................................................................................................................................................">
                </div>
            </div>
        </div>

        <!-- SIGNATURE BLOCK PAGE 1 -->
        <div class="row signature-block">
            <div class="col-4">
                <div>經理/ Giám Đốc</div>
                <div class="signature-space"></div>
            </div>
            <div class="col-4">
                <div>會計/ Kế toán</div>
                <div class="signature-space"></div>
            </div>
            <div class="col-4">
                <div>客戶確認請簽回/ Khách hàng xác nhận</div>
                <div class="signature-space"></div>
            </div>
        </div>

        <!-- ================= PAGE 2 (TECHNICAL SPECS & IMAGE) ================= -->
        <div class="page-break">
            <div class="text-center fw-bold fs-6 mb-3 text-uppercase border-bottom pb-2">
                Thông số kỹ thuật & Hình ảnh phụ kiện sửa chữa (Trang 2)
            </div>

            <table class="table-quote table-sm">
                <thead>
                    <tr>
                        <th style="width: 9rem;">Hãng Cân<br>Thương Hiệu</th>
                        <th style="width: 10rem;">Model<br>Tải Trọng</th>
                        <th style="width: 14rem;">Hình Ảnh</th>
                        <th>Thông số kỹ thuật và Tính năng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle fw-bold">
                            <input type="text" id="specBrand" class="form-control form-control-sm border-0 text-center fw-bold bg-transparent" value="Shimadzu Nhật">
                        </td>
                        <td class="text-center align-middle fw-bold position-relative">
                            <div class="autocomplete-wrapper">
                                <textarea id="specModel" class="form-control form-control-sm border-0 text-center fw-bold bg-transparent" 
                                          rows="3" autocomplete="off" placeholder="Gõ model..." 
                                          oninput="searchTechSpec(this.value)">Display UX/UW UP-X/UP-Y</textarea>
                                <div id="specSuggestions" class="autocomplete-dropdown d-none"></div>
                            </div>
                        </td>
                        <td class="text-center align-middle p-2">
                            <!-- Image Display Container -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="border rounded p-1 bg-light mb-1" style="width: 12rem; height: 7rem; display: flex; align-items: center; justify-content: center; overflow: hidden;" id="imagePreviewBox">
                                    <!-- Default SVG Display Component representation -->
                                    <svg viewBox="0 0 200 100" style="width: 100%; height: 100%;" id="defaultSpecSvg">
                                        <rect x="5" y="5" width="190" height="90" rx="4" fill="#2d3748" stroke="#1a202c" stroke-width="2"/>
                                        <rect x="20" y="15" width="160" height="60" rx="2" fill="#9ae6b4"/>
                                        <text x="30" y="55" font-family="monospace" font-size="28" font-weight="bold" fill="#1a202c">888.8 0.00 g</text>
                                        <path d="M 25 25 L 45 25" stroke="#1a202c" stroke-width="2"/>
                                        <rect x="15" y="80" width="170" height="10" fill="#4a5568"/>
                                        <text x="20" y="88" font-family="sans-serif" font-size="6" fill="#ffffff">DISPLAY BOARD UX/UW - CÁT VƯỢNG STAMP</text>
                                    </svg>
                                    <img id="customUploadedImg" class="img-fluid d-none" alt="Hình ảnh linh kiện">
                                </div>
                                <div class="editor-only d-print-none">
                                    <label class="btn btn-xs btn-outline-primary text-nowrap py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-upload me-1"></i> Tải ảnh khác
                                        <input type="file" accept="image/*" class="d-none" onchange="previewImage(this)">
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td class="align-top">
                            <textarea id="specDetails" class="form-control form-control-sm border-0 bg-transparent" rows="8" style="font-size: 0.82rem; line-height: 1.4;">- Display LCD (màn hình hiển thị số)
Bảo hành 12 tháng cho lỗi kỹ thuật như:
- Mất nét, mất số, mờ số
Trường hợp không bảo hành do lỗi người dùng:
- Bị cấn, rơi rớt, nứt, bể màn hình display.
- Dùng hóa chất lau màn hình dislay
- Board mạch bị dính chất lỏng, hóa chất làm hư màn hình display</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>
</div>

@push('scripts')
<script>
    let dbCustomersList = [];
    let dbProductsList = [];
    let dbTechSpecsList = [];

    const defaultItems = [
        {
            brand: 'Jadever',
            model: 'JWI-3100',
            description: 'cân điện tử 150kg/10g (230050201)\n- Hư nguồn (BH 01 tháng), hiệu chuẩn cân (bị sai kg)',
            price: 750000,
            qty: 1,
            unit: 'Cái'
        },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' }
    ];

    let currentItems = JSON.parse(JSON.stringify(defaultItems));
    let isEditLocked = false;

    // Render table rows
    function renderItems() {
        const tbody = document.getElementById('tableItemsBody');
        tbody.innerHTML = '';

        currentItems.forEach((item, index) => {
            const tr = document.createElement('tr');
            const lineTotal = item.price * item.qty;

            tr.innerHTML = `
                <td class="text-center fw-bold align-middle">${index + 1}</td>
                <td class="align-middle">
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1" 
                           value="${escapeHtml(item.brand)}" onchange="updateItem(${index}, 'brand', this.value)">
                </td>
                <td class="align-middle position-relative">
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1 fw-bold" 
                               value="${escapeHtml(item.model)}" 
                               placeholder="Gõ model..."
                               autocomplete="off"
                               oninput="searchProductForRow(${index}, this.value, this)"
                               onchange="updateItem(${index}, 'model', this.value)">
                        <div id="productSuggestionsRow${index}" class="autocomplete-dropdown d-none"></div>
                    </div>
                </td>
                <td class="align-middle">
                    <textarea class="form-control form-control-sm border-0 bg-transparent p-1" rows="2" style="font-size: 0.82rem;"
                              onchange="updateItem(${index}, 'description', this.value)">${escapeHtml(item.description)}</textarea>
                </td>
                <td class="align-middle text-end">
                    <input type="text" class="form-control form-control-sm border-0 bg-transparent text-end px-1" 
                           value="${item.price > 0 ? formatNumber(item.price) : '-'}" 
                           onfocus="this.value = currentItems[${index}].price || ''"
                           onblur="updateItemPrice(${index}, this.value)">
                </td>
                <td class="align-middle text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <input type="number" class="form-control form-control-sm border-0 bg-transparent text-center px-0" style="width: 2.2rem;" 
                               value="${item.qty > 0 ? item.qty : ''}" onchange="updateItem(${index}, 'qty', parseFloat(this.value) || 0)">
                        <span style="font-size: 0.75rem;">${item.qty > 0 ? (item.unit || 'Cái') : ''}</span>
                    </div>
                </td>
                <td class="align-middle text-end fw-bold">
                    ${lineTotal > 0 ? formatNumber(lineTotal) : '-'}
                </td>
                <td class="align-middle text-center editor-only d-print-none">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeItem(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        calculateTotals();
    }

    function updateItem(index, field, value) {
        currentItems[index][field] = value;
        calculateTotals();
    }

    function updateItemPrice(index, valStr) {
        const rawVal = parseFloat(valStr.replace(/[^0-9]/g, '')) || 0;
        currentItems[index].price = rawVal;
        renderItems();
    }

    function addNewRow() {
        currentItems.push({ brand: '', model: '', description: '', price: 0, qty: 1, unit: 'Cái' });
        renderItems();
    }

    function removeItem(index) {
        if (currentItems.length <= 1) {
            currentItems[0] = { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' };
        } else {
            currentItems.splice(index, 1);
        }
        renderItems();
    }

    function calculateTotals() {
        let subtotal = 0;
        currentItems.forEach(item => {
            subtotal += (item.price || 0) * (item.qty || 0);
        });

        const vatPercent = parseFloat(document.getElementById('vatPercent').value) || 0;
        const vatAmount = Math.round(subtotal * (vatPercent / 100));
        const grandTotal = subtotal + vatAmount;

        document.getElementById('subtotalCell').innerText = subtotal > 0 ? formatNumber(subtotal) : '0';
        document.getElementById('vatCell').innerText = vatAmount > 0 ? formatNumber(vatAmount) : '0';
        document.getElementById('grandTotalCell').innerText = grandTotal > 0 ? formatNumber(grandTotal) : '0';
    }

    function formatNumber(num) {
        return num.toLocaleString('vi-VN');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/&/g, "&amp;")
                   .replace(/</g, "&lt;")
                   .replace(/>/g, "&gt;")
                   .replace(/"/g, "&quot;")
                   .replace(/'/g, "&#039;");
    }

    function resetToDefaultSample() {
        currentItems = JSON.parse(JSON.stringify(defaultItems));
        document.getElementById('quoteNo').value = '2008097579';
        document.getElementById('quoteDate').value = '2026/06/15';
        document.getElementById('salesRep').value = 'Thuận 0919679246';
        document.getElementById('customerName').value = 'Công Ty Cổ Phần TuiCo 3';
        document.getElementById('contactPerson').value = 'Chị Hằng (thu mua) 0989 169170 , Chị Vân';
        document.getElementById('tel').value = '02513 671222';
        document.getElementById('fax').value = '02513 671666, 02513 671345';
        document.getElementById('address').value = 'Lô đất số 1-16, KCN Hố Nai, Phường Hố Nai, Thành Phố Đồng Nai, Việt Nam';
        document.getElementById('socialContact').value = 'Zalo, MinhHangCao, hang@tuico.com';
        document.getElementById('vatPercent').value = '8';
        renderItems();
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('defaultSpecSvg').classList.add('d-none');
                const img = document.getElementById('customUploadedImg');
                img.src = e.target.result;
                img.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleEditMode() {
        isEditLocked = !isEditLocked;
        const btn = document.getElementById('toggleEditBtn');
        const inputs = document.querySelectorAll('#quotationForm input, #quotationForm textarea');

        if (isEditLocked) {
            btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Chế độ Khóa (Chỉ Xem)';
            btn.classList.replace('btn-outline-success', 'btn-warning');
            inputs.forEach(i => i.setAttribute('readonly', true));
        } else {
            btn.innerHTML = '<i class="bi bi-pencil-square me-1"></i> Khóa / Sửa Form';
            btn.classList.replace('btn-warning', 'btn-outline-success');
            inputs.forEach(i => i.removeAttribute('readonly'));
        }
    }

    // Helper: Normalize string for flexible search (tuico3 matches Tuico 3)
    function normalizeStr(str) {
        if (!str) return '';
        return str.toLowerCase()
                  .normalize("NFD")
                  .replace(/[\u0300-\u036f]/g, "")
                  .replace(/[\s\-_.,]/g, "");
    }

    // 1. AUTOCOMPLETE: CUSTOMER SEARCH
    function searchCustomer(query) {
        const drop = document.getElementById('customerSuggestions');
        const cleanQuery = normalizeStr(query);

        if (!cleanQuery) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        const matches = dbCustomersList.filter(c => {
            const normName = normalizeStr(c.name);
            const normCode = normalizeStr(c.code);
            const normContact = normalizeStr(c.contact_person);
            const normTel = normalizeStr(c.tel);
            return normName.includes(cleanQuery) || normCode.includes(cleanQuery) || normContact.includes(cleanQuery) || normTel.includes(cleanQuery);
        });

        if (matches.length === 0) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        drop.innerHTML = '';
        matches.forEach(c => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="fw-bold text-primary"><i class="bi bi-building me-1"></i> ${escapeHtml(c.name)}</div>
                <div class="small text-secondary"><i class="bi bi-person me-1"></i> ${escapeHtml(c.contact_person || 'N/A')} - SĐT: ${escapeHtml(c.tel || 'N/A')}</div>
                <div class="small text-muted text-truncate"><i class="bi bi-geo-alt me-1"></i> ${escapeHtml(c.address || '')}</div>
            `;
            item.onclick = function() {
                document.getElementById('customerName').value = c.name || '';
                document.getElementById('contactPerson').value = c.contact_person || '';
                document.getElementById('tel').value = c.tel || '';
                document.getElementById('fax').value = c.fax || '';
                document.getElementById('address').value = c.address || '';
                document.getElementById('socialContact').value = c.social_contact || '';
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 2. AUTOCOMPLETE: PRODUCT ROW SEARCH (TABLE 1)
    function searchProductForRow(index, query, inputElem) {
        const drop = document.getElementById(`productSuggestionsRow${index}`);
        const cleanQuery = normalizeStr(query);

        if (!cleanQuery) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        const matches = dbProductsList.filter(p => {
            const normModel = normalizeStr(p.model);
            const normBrand = normalizeStr(p.brand);
            const normCode = normalizeStr(p.code);
            const normDesc = normalizeStr(p.description);
            return normModel.includes(cleanQuery) || normBrand.includes(cleanQuery) || normCode.includes(cleanQuery) || normDesc.includes(cleanQuery);
        });

        if (matches.length === 0) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        drop.innerHTML = '';
        matches.forEach(p => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="fw-bold text-dark"><span class="badge bg-primary-subtle text-primary border">${escapeHtml(p.brand || 'Khác')}</span> ${escapeHtml(p.model)}</div>
                <div class="small text-success fw-bold">${p.price > 0 ? formatNumber(p.price) + ' đ' : 'Báo giá'} - ĐVT: ${escapeHtml(p.unit || 'Cái')}</div>
                <div class="small text-secondary text-truncate">${escapeHtml(p.description || '')}</div>
            `;
            item.onclick = function() {
                currentItems[index].brand = p.brand || '';
                currentItems[index].model = p.model || '';
                currentItems[index].description = p.description || '';
                currentItems[index].price = parseFloat(p.price) || 0;
                currentItems[index].unit = p.unit || 'Cái';
                if (!currentItems[index].qty || currentItems[index].qty === 0) {
                    currentItems[index].qty = 1;
                }

                // If product has specs, also offer to populate Page 2
                if (p.specs) {
                    document.getElementById('specBrand').value = p.brand || 'Shimadzu Nhật';
                    document.getElementById('specModel').value = p.model || '';
                    document.getElementById('specDetails').value = p.specs;
                }

                drop.classList.add('d-none');
                renderItems();
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 3. AUTOCOMPLETE: TECH SPECS SEARCH (PAGE 2)
    function searchTechSpec(query) {
        const drop = document.getElementById('specSuggestions');
        const cleanQuery = normalizeStr(query);

        if (!cleanQuery) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        // Search combined tech specs and products
        const allSpecs = [...dbTechSpecsList, ...dbProductsList.filter(p => p.specs)];
        const matches = allSpecs.filter(s => {
            const normModel = normalizeStr(s.model);
            const normBrand = normalizeStr(s.brand);
            const normSpecs = normalizeStr(s.specs);
            return normModel.includes(cleanQuery) || normBrand.includes(cleanQuery) || normSpecs.includes(cleanQuery);
        });

        if (matches.length === 0) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        drop.innerHTML = '';
        matches.forEach(s => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="fw-bold text-info"><span class="badge bg-info-subtle text-info border">${escapeHtml(s.brand || 'Khác')}</span> ${escapeHtml(s.model)}</div>
                <div class="small text-secondary text-truncate" style="max-width: 22rem;">${escapeHtml(s.specs || '')}</div>
            `;
            item.onclick = function() {
                document.getElementById('specBrand').value = s.brand || '';
                document.getElementById('specModel').value = s.model || '';
                document.getElementById('specDetails').value = s.specs || '';
                if (s.image_path) {
                    document.getElementById('defaultSpecSvg').classList.add('d-none');
                    const img = document.getElementById('customUploadedImg');
                    img.src = s.image_path;
                    img.classList.remove('d-none');
                }
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-wrapper')) {
            document.querySelectorAll('.autocomplete-dropdown').forEach(d => d.classList.add('d-none'));
        }
    });

    // Load Database List
    async function loadDatabaseOptions() {
        try {
            const [custRes, prodRes, specRes] = await Promise.all([
                fetch('{{ route("api.customers") }}'),
                fetch('{{ route("api.products") }}'),
                fetch('{{ route("api.tech_specs") }}')
            ]);

            dbCustomersList = await custRes.json();
            dbProductsList = await prodRes.json();
            dbTechSpecsList = await specRes.json();
        } catch (e) {
            console.error('Không thể nạp dữ liệu từ CSDL:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderItems();
        loadDatabaseOptions();
    });
</script>
@endpush
@endsection
