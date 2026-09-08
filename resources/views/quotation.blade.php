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
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 10.5rem;
        display: inline-flex;
        align-items: center;
    }

    .info-label-sm {
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
        min-width: 5.2rem;
        display: inline-flex;
        align-items: center;
    }

    .metadata-label {
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    /* Table styling khớp với bản gốc */
    .table-quote {
        border-collapse: collapse !important;
        border: 0.08rem solid #000000 !important;
        margin-bottom: 0.5rem;
        width: 100% !important;
    }

    .table-quote th, 
    .table-quote td {
        border: 0.08rem solid #000000 !important;
        padding: 0.25rem 0.35rem;
        vertical-align: middle;
        font-size: 0.82rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
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

    /* Auto-expanding textarea styling (tự động to theo chiều dọc, không cuộn thanh lăn) */
    textarea.auto-expand {
        overflow-y: hidden !important;
        resize: none !important;
        min-height: 1.9rem;
        transition: height 0.05s ease-out;
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
        min-width: 16rem;
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
    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="window.print()">
        <i class="bi bi-printer-fill me-1"></i> In phiếu / Xuất PDF
    </button>
    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" onclick="saveTerms()">
        <i class="bi bi-floppy-fill me-1"></i> Lưu điều khoản
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" onclick="clearForm()">
        <i class="bi bi-eraser me-1"></i> Xóa trắng form
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm" onclick="loadSampleData()">
        <i class="bi bi-file-earmark-text me-1"></i> Tải dữ liệu mẫu
    </button>
    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" onclick="toggleEditMode()" id="toggleEditBtn">
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
                    <div><strong>辦公室/Văn Phòng:</strong> 12 Tô Ký, Phường Đông Hưng Thuận, TP. Hồ Chí Minh</div>
                    <div><strong>分支/Chi nhánh:</strong> 504/7 Nguyễn Văn Quá, Phường Đông Hưng Thuận, TP. Hồ Chí Minh</div>
                    <div><strong>稅號/Mã số thuế:</strong> 0306105289</div>
                    <div>
                        <strong>銷售部/Kinh doanh:</strong> 0919679246 &nbsp;-&nbsp;
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
            <div class="col-7">
                <div class="quote-main-title text-center">
                    <span style="font-family: 'SimSun', sans-serif;">维修报价单</span>/PHIẾU BÁO GIÁ SỬA CHỮA
                </div>
            </div>
            <div class="col-5 header-subtext ps-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="metadata-label"><strong>报价单号/Số báo giá:</strong></span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1 text-end" style="max-width: 8.5rem;">
                        <input type="text" id="quoteNo" class="form-control-sm-custom fw-bold text-end border-0 p-0 w-100" 
                               value="" placeholder="Nhập số..."
                               autocomplete="off"
                               onfocus="showQuoteNoSuggestions(this)"
                               oninput="showQuoteNoSuggestions(this)">
                        <div id="quoteNoSuggestions" class="autocomplete-dropdown d-none" style="min-width: 12rem; right: 0; left: auto;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="metadata-label"><strong>报价日期/Ngày báo giá:</strong></span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1 text-end" style="max-width: 8.5rem;">
                        <input type="text" id="quoteDate" class="form-control-sm-custom text-end border-0 p-0 w-100" 
                               value="{{ date('Y/m/d') }}"
                               autocomplete="off"
                               onfocus="showQuoteDateSuggestions(this)"
                               oninput="showQuoteDateSuggestions(this)">
                        <div id="quoteDateSuggestions" class="autocomplete-dropdown d-none" style="min-width: 10rem; right: 0; left: auto;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="metadata-label"><strong>负责业务/NV:</strong></span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1 text-end" style="max-width: 8.5rem;">
                        <input type="text" id="salesRep" class="form-control-sm-custom text-end border-0 p-0 w-100" 
                               value="" placeholder="Tên NV - SĐT"
                               autocomplete="off"
                               onfocus="showSalesRepSuggestions(this)"
                               oninput="showSalesRepSuggestions(this)">
                        <div id="salesRepSuggestions" class="autocomplete-dropdown d-none" style="min-width: 14rem; right: 0; left: auto;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="metadata-label"><strong>钱币/Tiền tệ:</strong></span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1 text-end" style="max-width: 4.5rem;">
                        <input type="text" id="currency" class="form-control-sm-custom text-end border-0 p-0 fw-bold w-100" 
                               value="VNĐ"
                               autocomplete="off"
                               onfocus="showCurrencySuggestions(this)"
                               oninput="showCurrencySuggestions(this)">
                        <div id="currencySuggestions" class="autocomplete-dropdown d-none" style="min-width: 8rem; right: 0; left: auto;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUSTOMER INFORMATION BLOCK (VỚI AUTOCOMPLETE TRỰC TIẾP KHI GÕ & LƯU CSDL NHANH) -->
        <div class="header-subtext mb-2 p-2 rounded bg-light border border-secondary-subtle position-relative">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom d-print-none">
                <span class="text-secondary fw-bold" style="font-size: 0.78rem;">
                    <i class="bi bi-person-lines-fill me-1"></i> THÔNG TIN KHÁCH HÀNG
                </span>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-bold" style="font-size: 0.75rem;" onclick="saveCustomerToDatabase()">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Lưu khách hàng này vào CSDL
                </button>
            </div>
            <div class="row g-1">
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">客户名称/Kính gửi:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="customerName" class="form-control form-control-sm border-0 bg-transparent fw-bold p-0" 
                               value="" placeholder="Nhập tên công ty..." 
                               autocomplete="off" 
                               onfocus="searchCustomer(this.value)"
                               oninput="searchCustomer(this.value)">
                        <div id="customerSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">联络人/Người liên hệ:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="contactPerson" class="form-control form-control-sm border-0 bg-transparent p-0" 
                               value="" placeholder="Người liên hệ..."
                               autocomplete="off"
                               onfocus="searchFieldOptions('contact_person', this.value, 'contactPersonSuggestions', this)"
                               oninput="searchFieldOptions('contact_person', this.value, 'contactPersonSuggestions', this)">
                        <div id="contactPersonSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-6 d-flex align-items-center">
                    <span class="info-label-sm">电话/Tel:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="tel" class="form-control form-control-sm border-0 bg-transparent p-0" 
                               value="" placeholder="Số điện thoại..."
                               autocomplete="off"
                               onfocus="searchFieldOptions('tel', this.value, 'telSuggestions', this)"
                               oninput="searchFieldOptions('tel', this.value, 'telSuggestions', this)">
                        <div id="telSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-6 d-flex align-items-center">
                    <span class="info-label-sm">传真/Fax:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="fax" class="form-control form-control-sm border-0 bg-transparent p-0" 
                               value="" placeholder="Số fax..."
                               autocomplete="off"
                               onfocus="searchFieldOptions('fax', this.value, 'faxSuggestions', this)"
                               oninput="searchFieldOptions('fax', this.value, 'faxSuggestions', this)">
                        <div id="faxSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">地址/Địa chỉ:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="address" class="form-control form-control-sm border-0 bg-transparent p-0" 
                               value="" placeholder="Địa chỉ..."
                               autocomplete="off"
                               onfocus="searchFieldOptions('address', this.value, 'addressSuggestions', this)"
                               oninput="searchFieldOptions('address', this.value, 'addressSuggestions', this)">
                        <div id="addressSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="info-label">Mail, Teams, Zalo:</span>
                    <div class="autocomplete-wrapper flex-grow-1 ms-1">
                        <input type="text" id="socialContact" class="form-control form-control-sm border-0 bg-transparent p-0" 
                               value="" placeholder="Zalo, Email, Teams..."
                               autocomplete="off"
                               onfocus="searchFieldOptions('social_contact', this.value, 'socialSuggestions', this)"
                               oninput="searchFieldOptions('social_contact', this.value, 'socialSuggestions', this)">
                        <div id="socialSuggestions" class="autocomplete-dropdown d-none"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GREETING MESSAGE -->
        <div class="fst-italic header-subtext mb-2">
            <div>Xin chân thành cảm ơn Quý khách hàng đã quan tâm ủng hộ sản phẩm và dịch vụ của chúng tôi.</div>
            <div style="font-family: 'SimSun', sans-serif;">感謝貴客戶已關心使用本公司的產品!</div>
        </div>

        <!-- QUOTATION ITEMS TABLE (TỐI ƯU CỘT DIỄN GIẢI RỘNG NHẤT) -->
        <table class="table-quote table-sm" id="itemsTable">
            <thead>
                <tr>
                    <th style="width: 3.2rem; min-width: 3.2rem;" class="text-center align-middle">STT</th>
                    <th style="width: 5.2rem; min-width: 5.2rem;" class="text-center align-middle">Hãng SX<br><span style="font-family: 'SimSun', sans-serif;">品牌</span></th>
                    <th style="width: 6.2rem; min-width: 6.2rem;" class="text-center align-middle">Model<br><span style="font-family: 'SimSun', sans-serif;">型號</span></th>
                    <th style="width: 100%;" class="text-center align-middle">Diễn giải<br><span style="font-family: 'SimSun', sans-serif;">說明</span></th>
                    <th style="width: 5.5rem; min-width: 5.5rem;" class="text-center align-middle">Đơn giá<br><span style="font-family: 'SimSun', sans-serif;">單價</span></th>
                    <th style="width: 4.2rem; min-width: 4.2rem;" class="text-center align-middle">Số lượng<br><span style="font-family: 'SimSun', sans-serif;">數量</span></th>
                    <th style="width: 6.2rem; min-width: 6.2rem;" class="text-center align-middle">Thành tiền<br><span style="font-family: 'SimSun', sans-serif;">總金額</span></th>
                    <th class="editor-only d-print-none text-center align-middle" style="width: 3.2rem; min-width: 3.2rem;">Xóa<br><span style="font-family: 'SimSun', sans-serif;">刪除</span></th>
                </tr>
            </thead>
            <tbody id="tableItemsBody">
                <!-- Rows rendered dynamically via JS -->
            </tbody>
            <tfoot>
                <!-- Subtotal Row -->
                <tr>
                    <td colspan="4" rowspan="3" class="align-top border-0">
                        <button type="button" class="btn btn-sm btn-outline-primary editor-only d-print-none mt-1 rounded-pill px-3" onclick="addNewRow()">
                            <i class="bi bi-plus-circle-fill me-1"></i> Thêm dòng sản phẩm
                        </button>
                    </td>
                    <td colspan="2" class="fw-bold text-end text-nowrap">報價金額/Số tiền</td>
                    <td class="fw-bold text-end" id="subtotalCell">0</td>
                    <td class="editor-only d-print-none p-0 text-center align-middle" style="width: 3.2rem;"></td>
                </tr>
                <!-- VAT Row -->
                <tr>
                    <td colspan="2" class="fw-bold text-end text-nowrap">
                        稅額/Thuế suất 
                        <input type="number" id="vatPercent" class="form-control-sm-custom d-inline-block text-center p-0 rounded" style="width: 2.5rem;" value="8" oninput="calculateTotals()"> %
                    </td>
                    <td class="fw-bold text-end" id="vatCell">0</td>
                    <td class="editor-only d-print-none p-0 text-center align-middle" style="width: 3.2rem;"></td>
                </tr>
                <!-- Grand Total Row -->
                <tr>
                    <td colspan="2" class="fw-bold text-end text-uppercase text-nowrap" style="font-size: 0.88rem;">報價總額/Tổng cộng</td>
                    <td class="fw-bold text-end text-danger" style="font-size: 0.95rem;" id="grandTotalCell">0</td>
                    <td class="editor-only d-print-none p-0 text-center align-middle" style="width: 3.2rem;"></td>
                </tr>
            </tfoot>
        </table>

        <!-- TERMS AND CONDITIONS (MỤC I - VII) (CHO PHÉP CHỈNH SỬA & LƯU LẠI) -->
        <div class="terms-section mt-3 position-relative">
            <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom d-print-none">
                <span class="text-secondary fw-bold" style="font-size: 0.78rem;">
                    <i class="bi bi-shield-check me-1"></i> ĐIỀU KHOẢN & QUY ĐỊNH BẢO HÀNH (Có thể chỉnh sửa trực tiếp)
                </span>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-xs btn-outline-success py-0 px-2 fw-bold" style="font-size: 0.75rem;" onclick="saveTerms()">
                        <i class="bi bi-floppy-fill me-1"></i> Lưu điều khoản này
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="resetTermsToDefault()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Mặc định
                    </button>
                </div>
            </div>
            <div class="row g-1">
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">I) Hình thức thanh toán:</span>
                    <input type="text" id="termPayment" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Tiền mặt hoặc chuyển khoản.">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">II) Thời hạn giao hàng:</span>
                    <input type="text" id="termDelivery" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Từ 02 đến 03 ngày, kể từ ngày xác nhận hồi fax.">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">III) Thời gian bảo hành:</span>
                    <input type="text" id="termWarranty" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="01 tháng, kể từ ngày giao hàng/sửa chữa.">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">IV)</span>
                    <input type="text" id="termValidity" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Báo giá có giá trị trong 30 ngày">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">V)</span>
                    <input type="text" id="termShipping" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Giá trên đã bao gồm phí vận chuyển thiết bị/sản phẩm đến khách hàng.">
                </div>
                <div class="col-12 d-flex align-items-center">
                    <span class="terms-title text-nowrap me-1">VI)</span>
                    <input type="text" id="termInspection" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Giá trên chưa bao gồm phí kiểm định/ hiệu chuẩn của đơn vị đo lường nhà nước cấp.">
                </div>
                <div class="col-12 mt-1">
                    <div class="terms-title mb-1">VII) Các hạng mục thay thế và thời gian bảo hành:</div>
                    <div class="ps-3">
                        <div class="d-flex align-items-center mb-1">
                            <strong class="text-nowrap me-1">1 .</strong>
                            <input type="text" id="termItem1" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Hiệu chỉnh phần mềm, hư nguồn, vệ sinh cân : 01 tháng">
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <strong class="text-nowrap me-1">2 .</strong>
                            <input type="text" id="termItem2" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Bình sạc, Jack LoadCell, Jack tín hiệu, Jack nguồn, nút nhấn, công tắc : 03 tháng (bảo hành khi bình không bị phù)">
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <strong class="text-nowrap me-1">3 .</strong>
                            <input type="text" id="termItem3" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Thay LoadCell (cảm biến lực), màn hình dislay, đầu đọc (đầu hiển thị số), board mạch, bàn phím : 12 tháng">
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <strong class="text-nowrap me-1">4 .</strong>
                            <input type="text" id="termItem4" class="form-control form-control-sm border-0 bg-transparent p-0 flex-grow-1" value="Adaptor, cục sạc, dây nguồn, dĩa cân, cổ cân, chân đế cân : không thuộc phạm vi bảo hành">
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-1 d-flex align-items-center">
                    <input type="text" id="termFooterNote" class="form-control form-control-sm border-0 bg-transparent p-0 fw-bold flex-grow-1" value="* Nhận bảo hành, bảo trì, sửa chữa tận nơi các loại cân điện tử với giá cả phải chăng.">
                </div>
                <div class="col-12 mt-1 d-flex align-items-center">
                    <span class="fw-bold text-nowrap" style="min-width: 14rem;">客户提意见/Ý kiến khách hàng:</span>
                    <input type="text" id="termCustomerFeedback" class="form-control form-control-sm border-0 border-bottom bg-transparent p-0 ms-1" placeholder="......................................................................................................................................................................................">
                </div>
            </div>
        </div>

        <!-- SIGNATURE BLOCK PAGE 1 -->
        <div class="row signature-block">
            <div class="col-4">
                <div class="text-nowrap">經理/Giám Đốc</div>
                <div class="signature-space"></div>
            </div>
            <div class="col-4">
                <div class="text-nowrap">會計/Kế toán</div>
                <div class="signature-space"></div>
            </div>
            <div class="col-4">
                <div class="text-nowrap">客戶確認請簽回/Khách hàng xác nhận</div>
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
                        <th style="width: 7.5rem; min-width: 7.5rem;" class="text-center align-middle">Hãng Cân<br>Thương Hiệu</th>
                        <th style="width: 8.5rem; min-width: 8.5rem;" class="text-center align-middle">Model<br>Tải Trọng</th>
                        <th style="width: 12rem; min-width: 12rem;" class="text-center align-middle">Hình Ảnh</th>
                        <th style="width: 100%;" class="text-center align-middle">Thông số kỹ thuật và Tính năng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle fw-bold position-relative">
                            <div class="autocomplete-wrapper">
                                <input type="text" id="specBrand" class="form-control form-control-sm border-0 text-center fw-bold bg-transparent" 
                                       value="" placeholder="Hãng..."
                                       autocomplete="off"
                                       onfocus="showSpecBrandSuggestions(this)"
                                       oninput="showSpecBrandSuggestions(this)">
                                <div id="specBrandSuggestions" class="autocomplete-dropdown d-none"></div>
                            </div>
                        </td>
                        <td class="text-center align-middle fw-bold position-relative">
                            <div class="autocomplete-wrapper">
                                <textarea id="specModel" class="form-control form-control-sm border-0 text-center fw-bold bg-transparent auto-expand w-100 p-1" 
                                          rows="1" autocomplete="off" placeholder="Gõ model..." 
                                          style="font-size: 0.82rem; resize: none; overflow-y: hidden; min-height: 1.9rem;"
                                          onfocus="autoResizeTextarea(this); searchTechSpec(this.value)"
                                          oninput="autoResizeTextarea(this); searchTechSpec(this.value)"
                                          onchange="autoResizeTextarea(this)"></textarea>
                                <div id="specSuggestions" class="autocomplete-dropdown d-none"></div>
                            </div>
                        </td>
                        <td class="text-center align-middle p-2">
                            <!-- Image Display Container -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="border rounded p-1 bg-light mb-1" style="width: 11rem; height: 6.5rem; display: flex; align-items: center; justify-content: center; overflow: hidden;" id="imagePreviewBox">
                                    <div id="defaultSpecSvg" class="text-center text-muted p-2">
                                        <i class="bi bi-image" style="font-size: 1.8rem;"></i>
                                        <div style="font-size: 0.72rem;">Chưa chọn hình ảnh</div>
                                    </div>
                                    <img id="customUploadedImg" class="img-fluid d-none" alt="Hình ảnh linh kiện">
                                </div>
                                <div class="editor-only d-print-none">
                                    <label class="btn btn-xs btn-outline-primary text-nowrap py-0 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-upload me-1"></i> Tải ảnh
                                        <input type="file" accept="image/*" class="d-none" onchange="previewImage(this)">
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td class="align-top position-relative p-1">
                            <div class="autocomplete-wrapper">
                                <textarea id="specDetails" class="form-control form-control-sm border-0 bg-transparent auto-expand w-100 p-1" rows="1" style="font-size: 0.82rem; line-height: 1.4; resize: none; overflow-y: hidden; min-height: 2.2rem;" 
                                          placeholder="Nhập thông số kỹ thuật và tính năng, chế độ bảo hành..."
                                          autocomplete="off"
                                          onfocus="autoResizeTextarea(this); showSpecDetailTemplates(this)"
                                          oninput="autoResizeTextarea(this); showSpecDetailTemplates(this)"
                                          onchange="autoResizeTextarea(this)"></textarea>
                                <div id="specDetailsSuggestions" class="autocomplete-dropdown d-none" style="min-width: 25rem;"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </form>
</div>

@push('scripts')
<script>
    const commonDescriptions = [
        'cân điện tử 150kg/10g - Hư nguồn (BH 01 tháng), hiệu chuẩn cân (bị sai kg)',
        'Thay LoadCell (cảm biến lực), hiệu chuẩn chuẩn F1',
        'Thay màn hình Display LCD (hiển thị số), vệ sinh board mạch',
        'Thay bình sạc 6V/4Ah, sửa Jack sạc nguồn (bảo hành 3 tháng)',
        'Hiệu chỉnh phần mềm, cân chỉnh góc, dán tem bảo hành',
        'Thay bo mạch chủ (Mainboard), thay phím bấm cảm ứng',
        'Thay đầu đọc hiển thị số (Indicator) kết nối máy tính',
        'Sửa bộ nguồn Adapter, thay pin sạc, căn chỉnh tải',
        'Bảo dưỡng tổng thể, vệ sinh cân, hiệu chuẩn độ chính xác',
        'Cung cấp quả cân chuẩn và kiểm định đo lường'
    ];

    const commonUnits = ['Cái', 'Bộ', 'Chiếc', 'Mét', 'Quả', 'Lần', 'Gói', 'Sợi'];

    const defaultItems = [
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
        { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' }
    ];

    let currentItems = JSON.parse(JSON.stringify(defaultItems));
    let isEditLocked = false;

    // Toast Notification Helper
    function showToast(message, type = 'success') {
        let toastContainer = document.getElementById('toastNotificationContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastNotificationContainer';
            toastContainer.className = 'position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast_' + Date.now();
        const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
        const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');

        const toastEl = document.createElement('div');
        toastEl.id = toastId;
        toastEl.className = `toast align-items-center ${bgClass} border-0 show shadow-lg mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="toast-body fw-bold">
                    <i class="bi ${icon} me-2 fs-6"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        setTimeout(() => {
            if (toastEl && toastEl.parentNode) {
                toastEl.remove();
            }
        }, 4000);
    }

    // Tự động điều chỉnh chiều cao của Textarea khi nhập nội dung dài (không bị con lăn cuộn)
    function autoResizeTextarea(el) {
        if (!el) return;
        el.style.height = 'auto';
        const scrollH = el.scrollHeight;
        if (scrollH > 0) {
            el.style.height = (scrollH + 2) + 'px';
        }
    }

    function autoResizeAllTextareas() {
        requestAnimationFrame(() => {
            document.querySelectorAll('textarea.auto-expand').forEach(el => {
                el.style.height = 'auto';
                const scrollH = el.scrollHeight;
                if (scrollH > 0) {
                    el.style.height = (scrollH + 2) + 'px';
                }
            });
        });
    }

    // Render table rows
    function renderItems() {
        const tbody = document.getElementById('tableItemsBody');
        tbody.innerHTML = '';

        currentItems.forEach((item, index) => {
            const tr = document.createElement('tr');
            const lineTotal = item.price * item.qty;

            tr.innerHTML = `
                <td class="text-center fw-bold align-middle px-1" style="font-size: 0.85rem;">${index + 1}</td>
                <td class="align-middle position-relative p-1">
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1 w-100" 
                               value="${escapeHtml(item.brand)}" placeholder="Hãng..."
                               autocomplete="off"
                               onfocus="searchBrandForRow(${index}, this.value, this)"
                               oninput="searchBrandForRow(${index}, this.value, this)"
                               onchange="updateItem(${index}, 'brand', this.value)">
                        <div id="brandSuggestionsRow${index}" class="autocomplete-dropdown d-none"></div>
                    </div>
                </td>
                <td class="align-middle position-relative p-1">
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent text-center px-1 fw-bold w-100" 
                               value="${escapeHtml(item.model)}" 
                               placeholder="Model..."
                               autocomplete="off"
                               onfocus="searchProductForRow(${index}, this.value, this)"
                               oninput="searchProductForRow(${index}, this.value, this)"
                               onchange="updateItem(${index}, 'model', this.value)">
                        <div id="productSuggestionsRow${index}" class="autocomplete-dropdown d-none"></div>
                    </div>
                </td>
                <td class="align-middle position-relative p-1">
                    <div class="autocomplete-wrapper">
                        <textarea class="form-control form-control-sm border-0 bg-transparent p-1 w-100 auto-expand" rows="1" style="font-size: 0.82rem; resize: none; overflow-y: hidden; line-height: 1.35;"
                                  placeholder="Diễn giải nội dung sửa chữa, thay thế linh kiện..."
                                  autocomplete="off"
                                  onfocus="searchDescriptionForRow(${index}, this.value, this)"
                                  oninput="currentItems[${index}].description = this.value; autoResizeTextarea(this); searchDescriptionForRow(${index}, this.value, this)"
                                  onchange="updateItem(${index}, 'description', this.value)">${escapeHtml(item.description)}</textarea>
                        <div id="descSuggestionsRow${index}" class="autocomplete-dropdown d-none" style="min-width: 22rem;"></div>
                    </div>
                </td>
                <td class="align-middle text-end position-relative p-1">
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent text-end px-1 w-100" 
                               value="${item.price > 0 ? formatNumber(item.price) : '-'}" 
                               placeholder="0"
                               autocomplete="off"
                               onfocus="searchPriceForRow(${index}, this.value, this)"
                               onblur="updateItemPrice(${index}, this.value)">
                        <div id="priceSuggestionsRow${index}" class="autocomplete-dropdown d-none" style="min-width: 10rem; right: 0; left: auto;"></div>
                    </div>
                </td>
                <td class="align-middle text-center position-relative p-1">
                    <div class="d-flex align-items-center justify-content-center">
                        <input type="number" class="form-control form-control-sm border-0 bg-transparent text-center px-0" style="width: 2rem;" 
                               value="${item.qty > 0 ? item.qty : ''}" placeholder="0" onchange="updateItem(${index}, 'qty', parseFloat(this.value) || 0)">
                        <span class="badge bg-light text-secondary border px-1 py-1 ms-1 cursor-pointer" style="font-size: 0.72rem; cursor: pointer;" 
                               title="Bấm để đổi ĐVT" onclick="showUnitOptionsForRow(${index}, this)">
                            ${item.qty > 0 ? (item.unit || 'Cái') : 'ĐVT'}
                        </span>
                        <div id="unitSuggestionsRow${index}" class="autocomplete-dropdown d-none" style="min-width: 6rem; right: 0; left: auto;"></div>
                    </div>
                </td>
                <td class="align-middle text-end fw-bold p-1">
                    ${lineTotal > 0 ? formatNumber(lineTotal) : '-'}
                </td>
                <td class="align-middle text-center editor-only d-print-none p-1" style="width: 3.2rem;">
                    <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center p-0" style="font-size: 0.9rem; border-radius: 0.4rem; width: 2.3rem; height: 1.9rem; margin: 0 auto; border-width: 0.09rem;" title="Xóa dòng" onclick="removeItem(${index})">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            const ta = tr.querySelector('textarea.auto-expand');
            if (ta && item.description) {
                autoResizeTextarea(ta);
            }
        });

        calculateTotals();
        setTimeout(autoResizeAllTextareas, 20);
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
        return text.toString()
                   .replace(/&/g, "&amp;")
                   .replace(/</g, "&lt;")
                   .replace(/>/g, "&gt;")
                   .replace(/"/g, "&quot;")
                   .replace(/'/g, "&#039;");
    }

    function clearForm() {
        currentItems = [
            { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
            { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
            { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
            { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' },
            { brand: '', model: '', description: '', price: 0, qty: 0, unit: '' }
        ];
        document.getElementById('quoteNo').value = '';
        document.getElementById('quoteDate').value = '{{ date("Y/m/d") }}';
        document.getElementById('salesRep').value = '';
        document.getElementById('currency').value = 'VNĐ';
        document.getElementById('customerName').value = '';
        document.getElementById('contactPerson').value = '';
        document.getElementById('tel').value = '';
        document.getElementById('fax').value = '';
        document.getElementById('address').value = '';
        document.getElementById('socialContact').value = '';
        document.getElementById('vatPercent').value = '8';
        
        // Clear Page 2
        document.getElementById('specBrand').value = '';
        document.getElementById('specModel').value = '';
        document.getElementById('specDetails').value = '';
        document.getElementById('defaultSpecSvg').classList.remove('d-none');
        const img = document.getElementById('customUploadedImg');
        img.src = '';
        img.classList.add('d-none');

        renderItems();
        autoResizeAllTextareas();
        showToast('Đã xóa trắng toàn bộ nội dung phiếu báo giá!', 'info');
    }

    function loadSampleData() {
        currentItems = [
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
        
        document.getElementById('specBrand').value = 'Shimadzu Nhật';
        document.getElementById('specModel').value = 'Display UX/UW UP-X/UP-Y';
        document.getElementById('specDetails').value = `- Display LCD (màn hình hiển thị số)\nBảo hành 12 tháng cho lỗi kỹ thuật như:\n- Mất nét, mất số, mờ số\nTrường hợp không bảo hành do lỗi người dùng:\n- Bị cấn, rơi rớt, nứt, bể màn hình display.\n- Dùng hóa chất lau màn hình dislay\n- Board mạch bị dính chất lỏng, hóa chất làm hư màn hình display`;
        
        renderItems();
        autoResizeAllTextareas();
        showToast('Đã nạp dữ liệu mẫu ví dụ!', 'info');
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

    const defaultTerms = {
        payment: 'Tiền mặt hoặc chuyển khoản.',
        delivery: 'Từ 02 đến 03 ngày, kể từ ngày xác nhận hồi fax.',
        warranty: '01 tháng, kể từ ngày giao hàng/sửa chữa.',
        validity: 'Báo giá có giá trị trong 30 ngày',
        shipping: 'Giá trên đã bao gồm phí vận chuyển thiết bị/sản phẩm đến khách hàng.',
        inspection: 'Giá trên chưa bao gồm phí kiểm định/ hiệu chuẩn của đơn vị đo lường nhà nước cấp.',
        item1: 'Hiệu chỉnh phần mềm, hư nguồn, vệ sinh cân : 01 tháng',
        item2: 'Bình sạc, Jack LoadCell, Jack tín hiệu, Jack nguồn, nút nhấn, công tắc : 03 tháng (bảo hành khi bình không bị phù)',
        item3: 'Thay LoadCell (cảm biến lực), màn hình dislay, đầu đọc (đầu hiển thị số), board mạch, bàn phím : 12 tháng',
        item4: 'Adaptor, cục sạc, dây nguồn, dĩa cân, cổ cân, chân đế cân : không thuộc phạm vi bảo hành',
        footerNote: '* Nhận bảo hành, bảo trì, sửa chữa tận nơi các loại cân điện tử với giá cả phải chăng.'
    };

    function saveTerms() {
        const terms = {
            payment: document.getElementById('termPayment') ? document.getElementById('termPayment').value : defaultTerms.payment,
            delivery: document.getElementById('termDelivery') ? document.getElementById('termDelivery').value : defaultTerms.delivery,
            warranty: document.getElementById('termWarranty') ? document.getElementById('termWarranty').value : defaultTerms.warranty,
            validity: document.getElementById('termValidity') ? document.getElementById('termValidity').value : defaultTerms.validity,
            shipping: document.getElementById('termShipping') ? document.getElementById('termShipping').value : defaultTerms.shipping,
            inspection: document.getElementById('termInspection') ? document.getElementById('termInspection').value : defaultTerms.inspection,
            item1: document.getElementById('termItem1') ? document.getElementById('termItem1').value : defaultTerms.item1,
            item2: document.getElementById('termItem2') ? document.getElementById('termItem2').value : defaultTerms.item2,
            item3: document.getElementById('termItem3') ? document.getElementById('termItem3').value : defaultTerms.item3,
            item4: document.getElementById('termItem4') ? document.getElementById('termItem4').value : defaultTerms.item4,
            footerNote: document.getElementById('termFooterNote') ? document.getElementById('termFooterNote').value : defaultTerms.footerNote
        };
        localStorage.setItem('saved_quotation_terms', JSON.stringify(terms));
        showToast('Đã lưu mẫu điều khoản & bảo hành thành công! Các lần sau sẽ tự động nạp mẫu này.', 'success');
    }

    function loadSavedTerms() {
        const saved = localStorage.getItem('saved_quotation_terms');
        if (saved) {
            try {
                const terms = JSON.parse(saved);
                if (terms.payment && document.getElementById('termPayment')) document.getElementById('termPayment').value = terms.payment;
                if (terms.delivery && document.getElementById('termDelivery')) document.getElementById('termDelivery').value = terms.delivery;
                if (terms.warranty && document.getElementById('termWarranty')) document.getElementById('termWarranty').value = terms.warranty;
                if (terms.validity && document.getElementById('termValidity')) document.getElementById('termValidity').value = terms.validity;
                if (terms.shipping && document.getElementById('termShipping')) document.getElementById('termShipping').value = terms.shipping;
                if (terms.inspection && document.getElementById('termInspection')) document.getElementById('termInspection').value = terms.inspection;
                if (terms.item1 && document.getElementById('termItem1')) document.getElementById('termItem1').value = terms.item1;
                if (terms.item2 && document.getElementById('termItem2')) document.getElementById('termItem2').value = terms.item2;
                if (terms.item3 && document.getElementById('termItem3')) document.getElementById('termItem3').value = terms.item3;
                if (terms.item4 && document.getElementById('termItem4')) document.getElementById('termItem4').value = terms.item4;
                if (terms.footerNote && document.getElementById('termFooterNote')) document.getElementById('termFooterNote').value = terms.footerNote;
            } catch (e) {
                console.error('Error loading saved terms:', e);
            }
        }
    }

    function resetTermsToDefault() {
        if (document.getElementById('termPayment')) document.getElementById('termPayment').value = defaultTerms.payment;
        if (document.getElementById('termDelivery')) document.getElementById('termDelivery').value = defaultTerms.delivery;
        if (document.getElementById('termWarranty')) document.getElementById('termWarranty').value = defaultTerms.warranty;
        if (document.getElementById('termValidity')) document.getElementById('termValidity').value = defaultTerms.validity;
        if (document.getElementById('termShipping')) document.getElementById('termShipping').value = defaultTerms.shipping;
        if (document.getElementById('termInspection')) document.getElementById('termInspection').value = defaultTerms.inspection;
        if (document.getElementById('termItem1')) document.getElementById('termItem1').value = defaultTerms.item1;
        if (document.getElementById('termItem2')) document.getElementById('termItem2').value = defaultTerms.item2;
        if (document.getElementById('termItem3')) document.getElementById('termItem3').value = defaultTerms.item3;
        if (document.getElementById('termItem4')) document.getElementById('termItem4').value = defaultTerms.item4;
        if (document.getElementById('termFooterNote')) document.getElementById('termFooterNote').value = defaultTerms.footerNote;
        localStorage.removeItem('saved_quotation_terms');
        showToast('Đã khôi phục các điều khoản về mặc định gốc!', 'info');
    }

    // Helper: Normalize string for flexible search
    function normalizeStr(str) {
        if (!str) return '';
        return str.toString().toLowerCase()
                   .normalize("NFD")
                   .replace(/[\u0300-\u036f]/g, "")
                   .replace(/[\s\-_.,]/g, "");
    }

    function fillCustomerData(c) {
        document.getElementById('customerName').value = c.name || '';
        document.getElementById('contactPerson').value = c.contact_person || '';
        document.getElementById('tel').value = c.tel || '';
        document.getElementById('fax').value = c.fax || '';
        document.getElementById('address').value = c.address || '';
        document.getElementById('socialContact').value = c.social_contact || '';
    }

    // 1. AUTOCOMPLETE: CUSTOMER SEARCH
    function searchCustomer(query) {
        const drop = document.getElementById('customerSuggestions');
        const cleanQuery = normalizeStr(query);

        if (!cleanQuery) {
            if (dbCustomersList.length === 0) {
                drop.classList.add('d-none');
                drop.innerHTML = '';
                return;
            }
            drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-clock-history me-1"></i> Khách hàng có trong CSDL (Top 10):</div>`;
            const top10 = dbCustomersList.slice(0, 10);
            top10.forEach(c => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-primary"><i class="bi bi-building me-1"></i> ${escapeHtml(c.name)}</div>
                    <div class="small text-secondary"><i class="bi bi-person me-1"></i> ${escapeHtml(c.contact_person || 'N/A')} - SĐT: ${escapeHtml(c.tel || 'N/A')}</div>
                    <div class="small text-muted text-truncate"><i class="bi bi-geo-alt me-1"></i> ${escapeHtml(c.address || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillCustomerData(c);
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
            drop.classList.remove('d-none');
            return;
        }

        const matches = dbCustomersList.filter(c => {
            const normName = normalizeStr(c.name);
            const normCode = normalizeStr(c.code);
            const normContact = normalizeStr(c.contact_person);
            const normTel = normalizeStr(c.tel);
            return normName.includes(cleanQuery) || normCode.includes(cleanQuery) || normContact.includes(cleanQuery) || normTel.includes(cleanQuery);
        });

        drop.innerHTML = '';
        if (matches.length > 0) {
            matches.slice(0, 10).forEach(c => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-primary"><i class="bi bi-building me-1"></i> ${escapeHtml(c.name)}</div>
                    <div class="small text-secondary"><i class="bi bi-person me-1"></i> ${escapeHtml(c.contact_person || 'N/A')} - SĐT: ${escapeHtml(c.tel || 'N/A')}</div>
                    <div class="small text-muted text-truncate"><i class="bi bi-geo-alt me-1"></i> ${escapeHtml(c.address || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillCustomerData(c);
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
        }

        const newItem = document.createElement('div');
        newItem.className = 'autocomplete-item bg-primary-subtle text-primary border-top';
        newItem.innerHTML = `
            <div class="fw-bold"><i class="bi bi-plus-circle-fill me-1"></i> Dùng tên mới: "<strong>${escapeHtml(query)}</strong>"</div>
            <div class="small text-muted">Điền tiếp các ô bên dưới rồi bấm nút "Lưu khách hàng vào CSDL"</div>
        `;
        newItem.onmousedown = function(e) {
            e.preventDefault();
            document.getElementById('customerName').value = query;
            drop.classList.add('d-none');
        };
        drop.appendChild(newItem);

        drop.classList.remove('d-none');
    }

    // 2. AUTOCOMPLETE CHO TẤT CẢ CÁC Ô KHÁCH HÀNG (Người liên hệ, SĐT, Fax, Địa chỉ, Kênh liên hệ)
    // Khi chọn bất kỳ ô nào -> tự động điền TẤT CẢ các ô còn lại của khách hàng đó!
    function searchFieldOptions(fieldName, query, dropdownId, inputElem) {
        const drop = document.getElementById(dropdownId);
        const cleanQuery = normalizeStr(query);

        if (dbCustomersList.length === 0) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        // Tìm kiếm khách hàng có chứa giá trị trường này hoặc khớp từ khóa
        let matches = dbCustomersList.filter(c => {
            if (!cleanQuery) return !!c[fieldName];
            const val = normalizeStr(c[fieldName] || '');
            const cName = normalizeStr(c.name || '');
            const cContact = normalizeStr(c.contact_person || '');
            const cTel = normalizeStr(c.tel || '');
            return val.includes(cleanQuery) || cName.includes(cleanQuery) || cContact.includes(cleanQuery) || cTel.includes(cleanQuery);
        });

        // Nếu không có khớp trường cụ thể, lấy danh sách khách hàng chung
        if (matches.length === 0 && !cleanQuery) {
            matches = dbCustomersList;
        }

        if (matches.length === 0) {
            drop.classList.add('d-none');
            drop.innerHTML = '';
            return;
        }

        const fieldLabels = {
            'contact_person': 'Người liên hệ',
            'tel': 'SĐT',
            'fax': 'Số Fax',
            'address': 'Địa chỉ',
            'social_contact': 'Mail/Zalo'
        };
        const currentLabel = fieldLabels[fieldName] || 'Gợi ý';

        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-person-lines-fill me-1"></i> Chọn để tự động điền đủ thông tin khách hàng (${matches.length}):</div>`;
        matches.slice(0, 10).forEach(c => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            const highlightedVal = c[fieldName] ? `<span class="fw-bold text-primary">${escapeHtml(c[fieldName])}</span>` : `<span class="text-muted fst-italic">(Chưa có ${currentLabel})</span>`;
            
            item.innerHTML = `
                <div>${highlightedVal} <span class="badge bg-secondary-subtle text-secondary ms-1">${escapeHtml(c.name || 'Khách hàng')}</span></div>
                <div class="small text-secondary"><i class="bi bi-person me-1"></i> ${escapeHtml(c.contact_person || 'N/A')} - SĐT: ${escapeHtml(c.tel || 'N/A')}</div>
                <div class="small text-muted text-truncate"><i class="bi bi-geo-alt me-1"></i> ${escapeHtml(c.address || '')}</div>
            `;
            item.onmousedown = function(e) {
                e.preventDefault();
                fillCustomerData(c); // Tự động điền TẤT CẢ các thông tin còn lại!
                drop.classList.add('d-none');
                showToast(`Đã tự động điền thông tin: ${c.name}`, 'info');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // LƯU KHÁCH HÀNG MỚI VÀO CSDL
    async function saveCustomerToDatabase() {
        const name = document.getElementById('customerName').value.trim();
        const contact_person = document.getElementById('contactPerson').value.trim();
        const tel = document.getElementById('tel').value.trim();
        const fax = document.getElementById('fax').value.trim();
        const address = document.getElementById('address').value.trim();
        const social_contact = document.getElementById('socialContact').value.trim();

        if (!name) {
            showToast('Vui lòng nhập Tên công ty / Khách hàng trước khi lưu!', 'danger');
            document.getElementById('customerName').focus();
            return;
        }

        const exists = dbCustomersList.find(c => normalizeStr(c.name) === normalizeStr(name));
        if (exists) {
            if (!confirm(`Khách hàng "${name}" đã có trong CSDL. Bạn có chắc muốn lưu thêm bản ghi này không?`)) {
                return;
            }
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const response = await fetch('{{ route("customers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    name: name,
                    contact_person: contact_person,
                    tel: tel,
                    fax: fax,
                    address: address,
                    social_contact: social_contact
                })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                const newCustomer = data.customer || { name, contact_person, tel, fax, address, social_contact };
                dbCustomersList.unshift(newCustomer);
                showToast(`Đã lưu khách hàng "${name}" vào CSDL thành công!`, 'success');
            } else {
                showToast(data.message || 'Không thể lưu khách hàng!', 'danger');
            }
        } catch (err) {
            console.error('Lỗi lưu khách hàng:', err);
            showToast('Lỗi kết nối máy chủ khi lưu khách hàng!', 'danger');
        }
    }

    // 3. AUTOCOMPLETE CHO HÃNG SX (BRAND)
    // Khi chọn hãng hoặc sản phẩm thuộc hãng -> Tự động điền đầy đủ các ô còn lại của dòng!
    function searchBrandForRow(index, query, inputElem) {
        const drop = document.getElementById(`brandSuggestionsRow${index}`);
        const cleanQuery = normalizeStr(query);

        const allBrands = Array.from(new Set([
            ...popularBrands,
            ...dbProductsList.map(p => p.brand).filter(b => b && b.trim())
        ]));

        let filteredBrands = allBrands;
        if (cleanQuery) {
            filteredBrands = allBrands.filter(b => normalizeStr(b).includes(cleanQuery));
        }

        // Tìm các sản phẩm thuộc hãng này trong CSDL để người dùng có thể chọn 1 phát ăn ngay
        let matchingProducts = dbProductsList.filter(p => {
            if (!cleanQuery) return true;
            return normalizeStr(p.brand).includes(cleanQuery) || normalizeStr(p.model).includes(cleanQuery);
        });

        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-tag me-1"></i> Chọn hãng hoặc chọn nhanh sản phẩm:</div>`;
        
        // 1. Danh sách hãng
        const brandBadgeContainer = document.createElement('div');
        brandBadgeContainer.className = 'p-2 border-bottom d-flex flex-wrap gap-1 bg-white';
        filteredBrands.slice(0, 10).forEach(brandName => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary-subtle text-primary border cursor-pointer';
            badge.style.cursor = 'pointer';
            badge.innerText = brandName;
            badge.onmousedown = function(e) {
                e.preventDefault();
                currentItems[index].brand = brandName;
                inputElem.value = brandName;
                drop.classList.add('d-none');
                renderItems();
            };
            brandBadgeContainer.appendChild(badge);
        });
        drop.appendChild(brandBadgeContainer);

        // 2. Sản phẩm kèm thông tin đầy đủ để điền 1 phát cả dòng
        if (matchingProducts.length > 0) {
            matchingProducts.slice(0, 8).forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-dark"><span class="badge bg-primary-subtle text-primary border">${escapeHtml(p.brand || 'Khác')}</span> ${escapeHtml(p.model)}</div>
                    <div class="small text-success fw-bold">${p.price > 0 ? formatNumber(p.price) + ' đ' : 'Báo giá'} - ĐVT: ${escapeHtml(p.unit || 'Cái')}</div>
                    <div class="small text-secondary text-truncate">${escapeHtml(p.description || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillProductForRow(index, p); // Điền đầy đủ Hãng, Model, Diễn giải, Giá, ĐVT, Specs!
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
        }

        drop.classList.remove('d-none');
    }

    // 4. AUTOCOMPLETE CHO MODEL SẢN PHẨM
    function fillProductForRow(index, p) {
        currentItems[index].brand = p.brand || currentItems[index].brand || '';
        currentItems[index].model = p.model || '';
        currentItems[index].description = p.description || currentItems[index].description || '';
        currentItems[index].price = parseFloat(p.price) || currentItems[index].price || 0;
        currentItems[index].unit = p.unit || currentItems[index].unit || 'Cái';
        if (!currentItems[index].qty || currentItems[index].qty === 0) {
            currentItems[index].qty = 1;
        }

        if (p.specs) {
            document.getElementById('specBrand').value = p.brand || 'Shimadzu Nhật';
            document.getElementById('specModel').value = p.model || '';
            document.getElementById('specDetails').value = p.specs;
            autoResizeAllTextareas();
        }

        renderItems();
        autoResizeAllTextareas();
        showToast(`Đã tự động điền đầy đủ dòng sản phẩm: ${p.model}`, 'info');
    }

    function searchProductForRow(index, query, inputElem) {
        const drop = document.getElementById(`productSuggestionsRow${index}`);
        const cleanQuery = normalizeStr(query);

        if (!cleanQuery) {
            if (dbProductsList.length === 0) {
                drop.classList.add('d-none');
                drop.innerHTML = '';
                return;
            }
            drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-box-seam me-1"></i> Sản phẩm trong CSDL (Top 10) - Chọn để điền đủ dòng:</div>`;
            const top10 = dbProductsList.slice(0, 10);
            top10.forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-dark"><span class="badge bg-primary-subtle text-primary border">${escapeHtml(p.brand || 'Khác')}</span> ${escapeHtml(p.model)}</div>
                    <div class="small text-success fw-bold">${p.price > 0 ? formatNumber(p.price) + ' đ' : 'Báo giá'} - ĐVT: ${escapeHtml(p.unit || 'Cái')}</div>
                    <div class="small text-secondary text-truncate">${escapeHtml(p.description || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillProductForRow(index, p);
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
            drop.classList.remove('d-none');
            return;
        }

        const matches = dbProductsList.filter(p => {
            const normModel = normalizeStr(p.model);
            const normBrand = normalizeStr(p.brand);
            const normCode = normalizeStr(p.code);
            const normDesc = normalizeStr(p.description);
            return normModel.includes(cleanQuery) || normBrand.includes(cleanQuery) || normCode.includes(cleanQuery) || normDesc.includes(cleanQuery);
        });

        drop.innerHTML = '';
        if (matches.length > 0) {
            matches.slice(0, 10).forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-dark"><span class="badge bg-primary-subtle text-primary border">${escapeHtml(p.brand || 'Khác')}</span> ${escapeHtml(p.model)}</div>
                    <div class="small text-success fw-bold">${p.price > 0 ? formatNumber(p.price) + ' đ' : 'Báo giá'} - ĐVT: ${escapeHtml(p.unit || 'Cái')}</div>
                    <div class="small text-secondary text-truncate">${escapeHtml(p.description || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillProductForRow(index, p);
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
        }

        const newItem = document.createElement('div');
        newItem.className = 'autocomplete-item bg-primary-subtle text-primary border-top';
        newItem.innerHTML = `
            <div class="fw-bold"><i class="bi bi-plus-circle-fill me-1"></i> Dùng model mới: "<strong>${escapeHtml(query)}</strong>"</div>
        `;
        newItem.onmousedown = function(e) {
            e.preventDefault();
            currentItems[index].model = query;
            if (!currentItems[index].qty || currentItems[index].qty === 0) {
                currentItems[index].qty = 1;
            }
            drop.classList.add('d-none');
            renderItems();
        };
        drop.appendChild(newItem);

        drop.classList.remove('d-none');
    }

    // 5. AUTOCOMPLETE CHO CỘT DIỄN GIẢI (DESCRIPTION)
    // Khi chọn diễn giải -> Tự động điền sản phẩm tương ứng hoặc mẫu sửa chữa
    function searchDescriptionForRow(index, query, inputElem) {
        const drop = document.getElementById(`descSuggestionsRow${index}`);
        const cleanQuery = normalizeStr(query);

        // Sản phẩm có diễn giải khớp
        const matchingProducts = dbProductsList.filter(p => {
            if (!cleanQuery) return !!p.description;
            return normalizeStr(p.description || '').includes(cleanQuery) || normalizeStr(p.model || '').includes(cleanQuery);
        });

        const allDescs = Array.from(new Set([
            ...commonDescriptions,
            ...dbProductsList.map(p => p.description).filter(d => d && d.trim())
        ]));

        let filteredDescs = allDescs;
        if (cleanQuery) {
            filteredDescs = allDescs.filter(d => normalizeStr(d).includes(cleanQuery));
        }

        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-card-text me-1"></i> Chọn diễn giải / dịch vụ:</div>`;

        // Ưu tiên hiển thị sản phẩm từ CSDL nếu có
        if (matchingProducts.length > 0) {
            matchingProducts.slice(0, 5).forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item border-bottom bg-light-subtle';
                item.innerHTML = `
                    <div class="fw-bold text-primary"><i class="bi bi-box me-1"></i> ${escapeHtml(p.description)}</div>
                    <div class="small text-muted"><span class="badge bg-secondary-subtle text-dark">${escapeHtml(p.brand || 'Khác')}</span> Model: <strong>${escapeHtml(p.model || 'N/A')}</strong> - Giá: ${p.price > 0 ? formatNumber(p.price) + ' đ' : 'Báo giá'}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    fillProductForRow(index, p); // Tự động điền đầy đủ cả dòng!
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
        }

        // Các mẫu diễn giải sửa chữa thông dụng
        filteredDescs.slice(0, 8).forEach(desc => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `<div class="small text-dark text-wrap"><i class="bi bi-arrow-right-short text-success me-1"></i> ${escapeHtml(desc)}</div>`;
            item.onmousedown = function(e) {
                e.preventDefault();
                currentItems[index].description = desc;
                inputElem.value = desc;
                drop.classList.add('d-none');
                autoResizeTextarea(inputElem);
                renderItems();
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 6. AUTOCOMPLETE CHO ĐƠN GIÁ (PRICE)
    function searchPriceForRow(index, query, inputElem) {
        const drop = document.getElementById(`priceSuggestionsRow${index}`);
        inputElem.value = currentItems[index].price || '';

        const commonPrices = [50000, 100000, 150000, 200000, 350000, 500000, 750000, 1000000, 1200000, 1500000, 2000000, 2500000, 3500000, 5000000];

        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-cash-coin me-1"></i> Mức giá gợi ý:</div>`;
        
        // Nếu có sản phẩm trong CSDL có giá khớp model
        if (currentItems[index].model) {
            const pMatch = dbProductsList.find(p => normalizeStr(p.model) === normalizeStr(currentItems[index].model));
            if (pMatch && pMatch.price > 0) {
                const item = document.createElement('div');
                item.className = 'autocomplete-item bg-success-subtle text-success fw-bold';
                item.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Giá chuẩn CSDL (${pMatch.model}): ${formatNumber(pMatch.price)} đ`;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    currentItems[index].price = pMatch.price;
                    inputElem.value = formatNumber(pMatch.price);
                    drop.classList.add('d-none');
                    renderItems();
                };
                drop.appendChild(item);
            }
        }

        commonPrices.forEach(price => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item text-end fw-bold text-success';
            item.innerHTML = `${formatNumber(price)} đ`;
            item.onmousedown = function(e) {
                e.preventDefault();
                currentItems[index].price = price;
                inputElem.value = formatNumber(price);
                drop.classList.add('d-none');
                renderItems();
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 7. AUTOCOMPLETE CHO ĐƠN VỊ TÍNH (UNIT)
    function showUnitOptionsForRow(index, elem) {
        const drop = document.getElementById(`unitSuggestionsRow${index}`);
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold">Chọn ĐVT:</div>`;
        commonUnits.forEach(u => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item text-center fw-bold';
            item.innerText = u;
            item.onmousedown = function(e) {
                e.preventDefault();
                currentItems[index].unit = u;
                drop.classList.add('d-none');
                renderItems();
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 8. AUTOCOMPLETE CHO METADATA HEADER (Số báo giá, Ngày, NV, Tiền tệ)
    function showQuoteNoSuggestions(inputElem) {
        const drop = document.getElementById('quoteNoSuggestions');
        const todayStr = new Date().toISOString().slice(0,10).replace(/-/g,"");
        const suggestions = [
            `${todayStr}01`,
            `${todayStr}02`,
            `2008097579`,
            `BG-${todayStr}`
        ];
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-hash me-1"></i> Số báo giá gợi ý:</div>`;
        suggestions.forEach(no => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item fw-bold text-primary';
            item.innerText = no;
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = no;
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    function showQuoteDateSuggestions(inputElem) {
        const drop = document.getElementById('quoteDateSuggestions');
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');

        const suggestions = [
            `${y}/${m}/${d}`,
            `${d}/${m}/${y}`,
            `${y}-${m}-${d}`
        ];
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-calendar me-1"></i> Ngày gợi ý:</div>`;
        suggestions.forEach(dt => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item text-center fw-bold';
            item.innerText = dt;
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = dt;
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    function showSalesRepSuggestions(inputElem) {
        const drop = document.getElementById('salesRepSuggestions');
        const reps = [
            'Thuận 0919679246',
            'Kỹ Thuật 0916344106',
            'Kinh Doanh Cát Vượng',
            'Văn Phòng Cát Vượng'
        ];
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-person-badge me-1"></i> Nhân viên phụ trách:</div>`;
        reps.forEach(r => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item fw-bold text-dark';
            item.innerText = r;
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = r;
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    function showCurrencySuggestions(inputElem) {
        const drop = document.getElementById('currencySuggestions');
        const currencies = ['VNĐ', 'USD', 'RMB (¥)', 'EUR (€)'];
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold">Tiền tệ:</div>`;
        currencies.forEach(cur => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item text-center fw-bold text-primary';
            item.innerText = cur;
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = cur;
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    // 9. AUTOCOMPLETE CHO TRANG 2 (HÃNG, MODEL & THÔNG SỐ)
    // Khi chọn bất kỳ ô nào ở trang 2 -> tự động điền đầy đủ cả Hãng, Model, Thông số kỹ thuật & Ảnh!
    function showSpecBrandSuggestions(inputElem) {
        const drop = document.getElementById('specBrandSuggestions');
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold">Chọn thương hiệu hoặc sản phẩm:</div>`;
        
        // 1. Sản phẩm có thông số kỹ thuật
        const productsWithSpecs = dbProductsList.filter(p => p.specs || p.brand);
        if (productsWithSpecs.length > 0) {
            productsWithSpecs.slice(0, 6).forEach(p => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item border-bottom';
                item.innerHTML = `
                    <div class="fw-bold text-info"><span class="badge bg-info-subtle text-info border">${escapeHtml(p.brand || 'Khác')}</span> ${escapeHtml(p.model)}</div>
                    <div class="small text-secondary text-truncate">${escapeHtml(p.specs || p.description || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
                    document.getElementById('specBrand').value = p.brand || '';
                    document.getElementById('specModel').value = p.model || '';
                    document.getElementById('specDetails').value = p.specs || p.description || '';
                    if (p.image_path) {
                        document.getElementById('defaultSpecSvg').classList.add('d-none');
                        const img = document.getElementById('customUploadedImg');
                        img.src = p.image_path;
                        img.classList.remove('d-none');
                    }
                    drop.classList.add('d-none');
                };
                drop.appendChild(item);
            });
        }

        popularBrands.forEach(b => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item fw-bold';
            item.innerText = b;
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = b;
                drop.classList.add('d-none');
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    function showSpecDetailTemplates(inputElem) {
        const drop = document.getElementById('specDetailsSuggestions');
        const templates = [
            `- Display LCD (màn hình hiển thị số)\nBảo hành 12 tháng cho lỗi kỹ thuật như:\n- Mất nét, mất số, mờ số\nTrường hợp không bảo hành do lỗi người dùng:\n- Bị cấn, rơi rớt, nứt, bể màn hình display.\n- Dùng hóa chất lau màn hình dislay\n- Board mạch bị dính chất lỏng, hóa chất làm hư màn hình display`,
            `- Cảm biến lực LoadCell chuyên dụng\n- Bảo hành 12 tháng đối với lỗi kỹ thuật tín hiệu\n- Không bảo hành quá tải biến dạng cơ học`,
            `- Mainboard vi xử lý chính hãng\n- Bảo hành 12 tháng phần cứng\n- Khách hàng lưu ý tránh môi trường ẩm ướt`
        ];
        drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-file-text me-1"></i> Mẫu thông số bảo hành & Sản phẩm CSDL:</div>`;
        
        // Gợi ý từ CSDL
        const allSpecs = [...dbTechSpecsList, ...dbProductsList.filter(p => p.specs)];
        if (allSpecs.length > 0) {
            allSpecs.slice(0, 5).forEach(s => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item border-bottom';
                item.innerHTML = `
                    <div class="fw-bold text-primary">${escapeHtml(s.brand || '')} - ${escapeHtml(s.model || '')}</div>
                    <div class="small text-secondary text-truncate">${escapeHtml(s.specs || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
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
                    autoResizeAllTextareas();
                };
                drop.appendChild(item);
            });
        }

        templates.forEach((tpl, idx) => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item small text-truncate';
            item.innerText = `Mẫu ${idx + 1}: ` + tpl.split('\n')[0];
            item.onmousedown = function(e) {
                e.preventDefault();
                inputElem.value = tpl;
                drop.classList.add('d-none');
                autoResizeAllTextareas();
            };
            drop.appendChild(item);
        });
        drop.classList.remove('d-none');
    }

    function searchTechSpec(query) {
        const drop = document.getElementById('specSuggestions');
        const cleanQuery = normalizeStr(query);

        const allSpecs = [...dbTechSpecsList, ...dbProductsList.filter(p => p.specs)];

        if (!cleanQuery) {
            if (allSpecs.length === 0) {
                drop.classList.add('d-none');
                drop.innerHTML = '';
                return;
            }
            drop.innerHTML = `<div class="p-1 px-2 bg-light text-muted small border-bottom fw-bold"><i class="bi bi-cpu me-1"></i> Thông số linh kiện trong CSDL (Top 10):</div>`;
            const top10 = allSpecs.slice(0, 10);
            top10.forEach(s => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div class="fw-bold text-info"><span class="badge bg-info-subtle text-info border">${escapeHtml(s.brand || 'Khác')}</span> ${escapeHtml(s.model)}</div>
                    <div class="small text-secondary text-truncate" style="max-width: 22rem;">${escapeHtml(s.specs || '')}</div>
                `;
                item.onmousedown = function(e) {
                    e.preventDefault();
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
                    autoResizeAllTextareas();
                };
                drop.appendChild(item);
            });
            drop.classList.remove('d-none');
            return;
        }

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
        matches.slice(0, 10).forEach(s => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.innerHTML = `
                <div class="fw-bold text-info"><span class="badge bg-info-subtle text-info border">${escapeHtml(s.brand || 'Khác')}</span> ${escapeHtml(s.model)}</div>
                <div class="small text-secondary text-truncate" style="max-width: 22rem;">${escapeHtml(s.specs || '')}</div>
            `;
            item.onmousedown = function(e) {
                e.preventDefault();
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
                autoResizeAllTextareas();
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
        loadSavedTerms();
        setTimeout(autoResizeAllTextareas, 100);
    });
</script>
@endpush
@endsection
