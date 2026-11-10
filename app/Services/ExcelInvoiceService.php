<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use App\Models\Debt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ExcelInvoiceService
{
    /**
     * Đọc các dòng dữ liệu từ file Excel (.xlsx) hoặc .csv
     */
    public function readRows(string $filePath, string $extension): array
    {
        $extension = strtolower($extension);
        if ($extension === 'xlsx') {
            return $this->readXlsx($filePath);
        } elseif ($extension === 'csv' || $extension === 'txt') {
            return $this->readCsv($filePath);
        } else {
            // Thử đọc như CSV hoặc XLSX
            $rows = $this->readXlsx($filePath);
            if (!empty($rows)) {
                return $rows;
            }
            return $this->readCsv($filePath);
        }
    }

    /**
     * Đọc file .xlsx bằng ZipArchive và SimpleXML chuẩn
     */
    protected function readXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Đọc bảng chuỗi dùng chung (sharedStrings.xml)
        $sharedStrings = [];
        if (($sstContent = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $sstXml = @simplexml_load_string($sstContent);
            if ($sstXml && isset($sstXml->si)) {
                foreach ($sstXml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // 2. Tìm trang tính đầu tiên (worksheets/sheet1.xml)
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetContent === false) {
            // Tìm sheet bất kỳ
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('/xl\/worksheets\/sheet\d+\.xml/i', $stat['name'])) {
                    $sheetContent = $zip->getFromName($stat['name']);
                    break;
                }
            }
        }

        if ($sheetContent === false) {
            $zip->close();
            return [];
        }

        $sheetXml = @simplexml_load_string($sheetContent);
        $zip->close();

        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $rowData = [];
            $maxCol = 0;

            foreach ($row->c as $cell) {
                $cRef = (string)$cell['r'];
                $colIdx = $this->colLetterToIndex($cRef);
                $type = (string)$cell['t'];
                $val = (string)$cell->v;

                if ($type === 's') {
                    $val = $sharedStrings[(int)$val] ?? '';
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $val = (string)$cell->is->t;
                }

                $rowData[$colIdx] = trim((string)$val);
                if ($colIdx > $maxCol) {
                    $maxCol = $colIdx;
                }
            }

            // Điền đầy các ô trống
            $fullRow = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $fullRow[] = $rowData[$c] ?? '';
            }

            if (!empty(array_filter($fullRow, fn($v) => $v !== ''))) {
                $rows[] = $fullRow;
            }
        }

        return $rows;
    }

    /**
     * Chuyển ký tự cột (A, B, AA...) sang chỉ số số nguyên (0, 1, 26...)
     */
    protected function colLetterToIndex(string $cellRef): int
    {
        if (preg_match('/^([A-Z]+)/i', $cellRef, $matches)) {
            $letters = strtoupper($matches[1]);
            $len = strlen($letters);
            $num = 0;
            for ($i = 0; $i < $len; $i++) {
                $num = $num * 26 + (ord($letters[$i]) - 64);
            }
            return $num - 1;
        }
        return 0;
    }

    /**
     * Đọc file CSV có hỗ trợ UTF-8 BOM và tự nhận diện dấu phân cách (, ; tab)
     */
    protected function readCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        // Xóa BOM UTF-8 nếu có
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        // Nhận diện dấu phân cách
        $firstLine = strtok($content, "\r\n");
        $delimiter = ',';
        if ($firstLine) {
            $countComma = substr_count($firstLine, ',');
            $countSemi = substr_count($firstLine, ';');
            $countTab = substr_count($firstLine, "\t");
            if ($countSemi > $countComma && $countSemi > $countTab) {
                $delimiter = ';';
            } elseif ($countTab > $countComma && $countTab > $countSemi) {
                $delimiter = "\t";
            }
        }

        $temp = fopen('php://memory', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        $rows = [];
        while (($data = fgetcsv($temp, 0, $delimiter)) !== false) {
            if (!empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                $rows[] = array_map(fn($v) => trim((string)$v), $data);
            }
        }
        fclose($temp);

        return $rows;
    }

    /**
     * Chuẩn hóa chuỗi header tiếng Việt để so khớp linh hoạt
     */
    protected function normalizeHeader(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];
        foreach ($unicode as $nonAccent => $accent) {
            $str = preg_replace("/($accent)/i", $nonAccent, $str);
        }
        $str = preg_replace('/[^a-z0-9]/', '', $str);
        return $str;
    }

    /**
     * Nhận diện chỉ số cột dựa trên dòng tiêu đề
     */
    protected function mapColumns(array $headerRow): array
    {
        $mapping = [
            'invoice_number' => null,
            'partner_name' => null,
            'tax_code' => null,
            'phone' => null,
            'title' => null,
            'subtotal' => null,
            'tax_rate' => null,
            'tax_amount' => null,
            'paid_amount' => null,
            'issue_date' => null,
            'due_date' => null,
            'paid_date' => null,
            'is_concerned' => null,
            'notes' => null,
        ];

        foreach ($headerRow as $idx => $headerText) {
            $norm = $this->normalizeHeader($headerText);

            if ($mapping['invoice_number'] === null && (str_contains($norm, 'sohoadon') || str_contains($norm, 'sohd') || str_contains($norm, 'invoicenumber') || str_contains($norm, 'invoiceno'))) {
                $mapping['invoice_number'] = $idx;
            } elseif ($mapping['partner_name'] === null && (str_contains($norm, 'doanhnghiep') || str_contains($norm, 'doitac') || str_contains($norm, 'khachhang') || str_contains($norm, 'nhacungcap') || str_contains($norm, 'tendoitac') || str_contains($norm, 'tendoanhnghiep') || str_contains($norm, 'partner') || str_contains($norm, 'customer') || str_contains($norm, 'supplier'))) {
                $mapping['partner_name'] = $idx;
            } elseif ($mapping['tax_code'] === null && (str_contains($norm, 'masothue') || str_contains($norm, 'mst') || str_contains($norm, 'taxcode'))) {
                $mapping['tax_code'] = $idx;
            } elseif ($mapping['phone'] === null && (str_contains($norm, 'dienthoai') || str_contains($norm, 'sdt') || str_contains($norm, 'phone'))) {
                $mapping['phone'] = $idx;
            } elseif ($mapping['title'] === null && (str_contains($norm, 'diengiai') || str_contains($norm, 'noidung') || str_contains($norm, 'tieude') || str_contains($norm, 'tenhanghoa') || str_contains($norm, 'mota') || str_contains($norm, 'title') || str_contains($norm, 'description'))) {
                $mapping['title'] = $idx;
            } elseif ($mapping['subtotal'] === null && (str_contains($norm, 'tientruocthue') || str_contains($norm, 'thanhtien') || str_contains($norm, 'dongia') || str_contains($norm, 'tienhang') || str_contains($norm, 'subtotal') || (str_contains($norm, 'sotien') && !str_contains($norm, 'thue')))) {
                $mapping['subtotal'] = $idx;
            } elseif ($mapping['tax_rate'] === null && (str_contains($norm, 'thuesuat') || str_contains($norm, 'vat%') || str_contains($norm, 'thuesuatvat') || str_contains($norm, 'taxrate') || $norm === 'vat')) {
                $mapping['tax_rate'] = $idx;
            } elseif ($mapping['tax_amount'] === null && (str_contains($norm, 'tienthue') || str_contains($norm, 'tienvat') || str_contains($norm, 'taxamount') || str_contains($norm, 'vatamount'))) {
                $mapping['tax_amount'] = $idx;
            } elseif ($mapping['paid_amount'] === null && (str_contains($norm, 'dathanhtoan') || str_contains($norm, 'datra') || str_contains($norm, 'dathu') || str_contains($norm, 'sotiendatra') || str_contains($norm, 'paidamount') || str_contains($norm, 'paid'))) {
                $mapping['paid_amount'] = $idx;
            } elseif ($mapping['issue_date'] === null && (str_contains($norm, 'ngaylap') || str_contains($norm, 'ngayhd') || str_contains($norm, 'ngayhoadon') || str_contains($norm, 'issuedate') || $norm === 'ngay')) {
                $mapping['issue_date'] = $idx;
            } elseif ($mapping['due_date'] === null && (str_contains($norm, 'hanthanhtoan') || str_contains($norm, 'hantt') || str_contains($norm, 'duedate'))) {
                $mapping['due_date'] = $idx;
            } elseif ($mapping['paid_date'] === null && (str_contains($norm, 'ngaythanhtoan') || str_contains($norm, 'ngaytra') || str_contains($norm, 'paiddate'))) {
                $mapping['paid_date'] = $idx;
            } elseif ($mapping['is_concerned'] === null && (str_contains($norm, 'phanloai') || str_contains($norm, 'quantam') || str_contains($norm, 'isconcerned'))) {
                $mapping['is_concerned'] = $idx;
            } elseif ($mapping['notes'] === null && (str_contains($norm, 'ghichu') || str_contains($norm, 'notes') || str_contains($norm, 'note'))) {
                $mapping['notes'] = $idx;
            }
        }

        // Nếu không khớp theo tên, tự động fallback theo thứ tự chuẩn mẫu:
        // 0: Số HĐ, 1: Tên đối tác, 2: MST, 3: SĐT, 4: Diễn giải, 5: Tiền trước thuế, 6: VAT%, 7: Tiền thuế, 8: Đã trả, 9: Ngày lập, 10: Hạn TT, 11: Ngày trả, 12: Phân loại, 13: Ghi chú
        if ($mapping['partner_name'] === null && isset($headerRow[1])) {
            $mapping['invoice_number'] = $mapping['invoice_number'] ?? 0;
            $mapping['partner_name'] = $mapping['partner_name'] ?? 1;
            $mapping['tax_code'] = $mapping['tax_code'] ?? 2;
            $mapping['phone'] = $mapping['phone'] ?? 3;
            $mapping['title'] = $mapping['title'] ?? 4;
            $mapping['subtotal'] = $mapping['subtotal'] ?? 5;
            $mapping['tax_rate'] = $mapping['tax_rate'] ?? 6;
            $mapping['tax_amount'] = $mapping['tax_amount'] ?? 7;
            $mapping['paid_amount'] = $mapping['paid_amount'] ?? 8;
            $mapping['issue_date'] = $mapping['issue_date'] ?? 9;
            $mapping['due_date'] = $mapping['due_date'] ?? 10;
            $mapping['paid_date'] = $mapping['paid_date'] ?? 11;
            $mapping['is_concerned'] = $mapping['is_concerned'] ?? 12;
            $mapping['notes'] = $mapping['notes'] ?? 13;
        }

        return $mapping;
    }

    /**
     * Chuyển đổi ngày tháng linh hoạt từ Excel serial hoặc chuỗi dd/mm/yyyy
     */
    protected function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);

        // Trường hợp Excel lưu ngày dạng số serial (ví dụ: 45656)
        if (is_numeric($value) && (float)$value > 20000 && (float)$value < 65000) {
            $unixTimestamp = ((float)$value - 25569) * 86400;
            return gmdate('Y-m-d', (int)$unixTimestamp);
        }

        // Các định dạng thông dụng: dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd
        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'm/d/Y', 'Y-m-d H:i:s'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $value);
                if ($d && $d->year >= 2000 && $d->year <= 2100) {
                    return $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Tiếp tục thử định dạng khác
            }
        }

        // Thử hàm strtotime mặc định
        $t = strtotime($value);
        if ($t !== false) {
            return date('Y-m-d', $t);
        }

        return null;
    }

    /**
     * Chuyển chuỗi tiền tệ sang số thực
     */
    protected function parseNumber(?string $value): float
    {
        if ($value === null || trim($value) === '') {
            return 0.0;
        }
        // Xóa dấu phẩy, khoảng trắng, chữ đ hoặc vnđ
        $clean = preg_replace('/[^\d\.\-]/', '', str_replace(',', '', (string)$value));
        return (float)$clean;
    }

    /**
     * Tiến hành nhập toàn bộ hóa đơn từ mảng dòng đọc được
     */
    public function import(array $rows, int $companyId, string $flow, bool $syncDebt = true): array
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File không có dữ liệu nào để nhập.',
                'imported_count' => 0,
                'new_partners_count' => 0,
                'skipped_count' => 0,
                'errors' => [],
            ];
        }

        // Tìm dòng tiêu đề (thường là dòng 0 hoặc dòng chứa chữ đối tác/doanh nghiệp/hóa đơn)
        $headerIndex = 0;
        foreach ($rows as $idx => $r) {
            $joined = mb_strtolower(implode(' ', $r));
            if (str_contains($joined, 'hoa don') || str_contains($joined, 'doi tac') || str_contains($joined, 'doanh nghiep') || str_contains($joined, 'tien')) {
                $headerIndex = $idx;
                break;
            }
        }

        $headerRow = $rows[$headerIndex];
        $mapping = $this->mapColumns($headerRow);

        // 1. TỐI ƯU HIỆU NĂNG: Tải trước toàn bộ đối tác của công ty vào bộ nhớ RAM
        // Giúp loại bỏ hoàn toàn các truy vấn SELECT partner lặp đi lặp lại qua mạng Supabase
        $existingPartnersList = Partner::where('company_id', $companyId)->get();
        $partnersMap = [];
        foreach ($existingPartnersList as $p) {
            $partnersMap[mb_strtolower(trim($p->name))] = $p;
        }

        // 2. TỐI ƯU HIỆU NĂNG: Tải trước toàn bộ số hóa đơn đã tồn tại của công ty
        $existingInvoicesList = Invoice::where('company_id', $companyId)
            ->where('type', $flow)
            ->select('invoice_number', 'partner_id', 'issue_date')
            ->get();
        $invoicesMap = [];
        foreach ($existingInvoicesList as $inv) {
            $y = $inv->issue_date ? date('Y', strtotime((string)$inv->issue_date)) : '';
            $num = trim((string)$inv->invoice_number);
            if ($flow === 'in') {
                $invoicesMap[$inv->partner_id . '_' . $y . '_' . $num] = true;
            } else {
                $invoicesMap[$y . '_' . $num] = true;
            }
        }

        $importedCount = 0;
        $newPartnersCount = 0;
        $skippedCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            // Duyệt các dòng dữ liệu sau dòng tiêu đề
            for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNum = $i + 1;

                $partnerName = $mapping['partner_name'] !== null ? trim($row[$mapping['partner_name']] ?? '') : '';
                if ($partnerName === '') {
                    // Dòng rỗng hoặc thiếu tên đối tác -> Bỏ qua
                    continue;
                }

                $rawInvNumber = $mapping['invoice_number'] !== null ? trim($row[$mapping['invoice_number']] ?? '') : '';
                $taxCode = $mapping['tax_code'] !== null ? trim($row[$mapping['tax_code']] ?? '') : '';
                $phone = $mapping['phone'] !== null ? trim($row[$mapping['phone']] ?? '') : '';
                $title = $mapping['title'] !== null ? trim($row[$mapping['title']] ?? '') : '';
                $subtotal = $mapping['subtotal'] !== null ? $this->parseNumber($row[$mapping['subtotal']] ?? '0') : 0.0;
                $rawTaxRate = $mapping['tax_rate'] !== null ? trim($row[$mapping['tax_rate']] ?? '') : '';
                $taxRate = $rawTaxRate !== '' ? $this->parseNumber($rawTaxRate) : 8.0; // Mặc định 8%
                $rawTaxAmount = $mapping['tax_amount'] !== null ? trim($row[$mapping['tax_amount']] ?? '') : '';
                $taxAmount = $rawTaxAmount !== '' ? $this->parseNumber($rawTaxAmount) : round($subtotal * ($taxRate / 100), 2);
                $totalAmount = $subtotal + $taxAmount;

                $paidAmount = $mapping['paid_amount'] !== null ? $this->parseNumber($row[$mapping['paid_amount']] ?? '0') : 0.0;
                $issueDate = $mapping['issue_date'] !== null ? $this->parseDate($row[$mapping['issue_date']] ?? '') : null;
                if (!$issueDate) {
                    $issueDate = date('Y-m-d');
                }

                $dueDate = $mapping['due_date'] !== null ? $this->parseDate($row[$mapping['due_date']] ?? '') : null;
                $paidDate = $mapping['paid_date'] !== null ? $this->parseDate($row[$mapping['paid_date']] ?? '') : null;
                if ($paidAmount > 0 && !$paidDate) {
                    $paidDate = $issueDate;
                }

                $rawConcern = $mapping['is_concerned'] !== null ? trim((string)($row[$mapping['is_concerned']] ?? '')) : '';
                $isConcerned = true;
                if ($rawConcern === '0' || in_array(mb_strtolower($rawConcern), ['0', 'false', 'khong', 'khong quan tam', 'ko', 'k', 'khongquantam'])) {
                    $isConcerned = false;
                } elseif ($rawConcern === '1' || in_array(mb_strtolower($rawConcern), ['1', 'true', 'quan tam', 'quantam', 'co'])) {
                    $isConcerned = true;
                }

                $notes = $mapping['notes'] !== null ? trim($row[$mapping['notes']] ?? '') : '';

                // Xử lý đối tác qua bộ nhớ đệm
                $partnerKey = mb_strtolower($partnerName);
                if (isset($partnersMap[$partnerKey])) {
                    $partner = $partnersMap[$partnerKey];
                    // Cập nhật bổ sung MST hoặc SĐT nếu đối tác cũ chưa có
                    $updated = false;
                    if (empty($partner->tax_code) && !empty($taxCode)) {
                        $partner->tax_code = $taxCode;
                        $updated = true;
                    }
                    if (empty($partner->phone) && !empty($phone)) {
                        $partner->phone = $phone;
                        $updated = true;
                    }
                    if ($updated) {
                        $partner->save();
                    }
                } else {
                    $partner = Partner::create([
                        'company_id' => $companyId,
                        'name' => $partnerName,
                        'tax_code' => !empty($taxCode) ? $taxCode : null,
                        'phone' => !empty($phone) ? $phone : null,
                        'type' => $flow === 'out' ? 'customer' : 'supplier',
                    ]);
                    $partnersMap[$partnerKey] = $partner;
                    $newPartnersCount++;
                }

                // Xử lý số hóa đơn và kiểm tra trùng theo năm
                $issueYear = date('Y', strtotime($issueDate));
                $invoiceNumber = $rawInvNumber;

                if (empty($invoiceNumber)) {
                    $prefix = $flow === 'out' ? 'HD-XUAT' : 'HD-VAO';
                    do {
                        $invoiceNumber = $prefix . '-' . date('ymd', strtotime($issueDate)) . '-' . rand(1000, 9999);
                        $checkKey = $flow === 'in' ? $partner->id . '_' . $issueYear . '_' . $invoiceNumber : $issueYear . '_' . $invoiceNumber;
                    } while (isset($invoicesMap[$checkKey]));
                } else {
                    $checkKey = $flow === 'in' ? $partner->id . '_' . $issueYear . '_' . $invoiceNumber : $issueYear . '_' . $invoiceNumber;
                    if (isset($invoicesMap[$checkKey])) {
                        $skippedCount++;
                        $errors[] = "Dòng {$rowNum}: Số hóa đơn [{$invoiceNumber}] năm {$issueYear} đã tồn tại trong hệ thống (bỏ qua).";
                        continue;
                    }
                }

                // Ghi nhận số hóa đơn đã dùng vào bộ nhớ để chống trùng lặp ngay trong file
                $checkKey = $flow === 'in' ? $partner->id . '_' . $issueYear . '_' . $invoiceNumber : $issueYear . '_' . $invoiceNumber;
                $invoicesMap[$checkKey] = true;

                if (empty($title)) {
                    $title = "Hóa đơn {$invoiceNumber}";
                }

                // Trạng thái thanh toán
                if ($paidAmount > $totalAmount && $totalAmount > 0) {
                    $status = 'overpaid';
                } elseif ($paidAmount >= $totalAmount && $totalAmount > 0) {
                    $status = 'paid';
                    $paidAmount = $totalAmount;
                } elseif ($paidAmount > 0) {
                    $status = 'partial';
                } else {
                    $status = 'unpaid';
                    $paidAmount = 0.0;
                }

                $debtId = null;

                // Đồng bộ sang sổ theo dõi công nợ nếu được bật
                if ($syncDebt) {
                    $debtType = $flow === 'out' ? 'receivable' : 'payable';
                    $debtCode = 'CN-' . ($debtType === 'receivable' ? 'THU' : 'TRA') . '-' . date('ymd') . '-' . substr(md5(uniqid((string)mt_rand(), true)), 0, 6);

                    $debt = Debt::create([
                        'code' => $debtCode,
                        'company_id' => $companyId,
                        'partner_id' => $partner->id,
                        'type' => $debtType,
                        'title' => "Hóa đơn {$invoiceNumber}: " . $title,
                        'amount' => $totalAmount,
                        'paid_amount' => $paidAmount,
                        'issue_date' => $issueDate,
                        'due_date' => $dueDate,
                        'status' => $status === 'paid' ? 'completed' : ($paidAmount > 0 ? 'partial' : 'pending'),
                        'notes' => "Tự động tạo từ Excel cho hóa đơn {$invoiceNumber}",
                    ]);
                    $debtId = $debt->id;
                }

                Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'company_id' => $companyId,
                    'partner_id' => $partner->id,
                    'debt_id' => $debtId,
                    'type' => $flow,
                    'is_concerned' => $isConcerned,
                    'title' => $title,
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'paid_date' => $paidDate,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'notes' => $notes ?: null,
                ]);

                $importedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'new_partners_count' => $newPartnersCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Tạo file mẫu CSV (.csv) chuẩn UTF-8 có BOM (Excel mở trực tiếp 100% không lỗi font tiếng Việt)
     */
    public function generateTemplateCsv(string $outputPath, string $flow = 'in'): bool
    {
        $headers = [
            'Số Hóa Đơn',
            'Tên Doanh Nghiệp / Đối Tác',
            'Mã Số Thuế',
            'Số Điện Thoại',
            'Nội Dung Diễn Giải',
            'Tiền Trước Thuế',
            'Thuế Suất VAT (%)',
            'Tiền Thuế VAT',
            'Đã Thanh Toán',
            'Ngày Lập (dd/mm/yyyy)',
            'Hạn Thanh Toán (dd/mm/yyyy)',
            'Ngày Thanh Toán (dd/mm/yyyy)',
            'Phân Loại (1=Quan tâm, 0=Không)',
            'Ghi Chú',
        ];

        $today = date('d/m/Y');
        $after30Days = date('d/m/Y', strtotime('+30 days'));

        $sampleRows = [
            [
                '51567',
                $flow === 'out' ? 'Công Ty TNHH Đại Phát' : 'Bảo Gia Trần',
                '0312345678',
                '0901234567',
                $flow === 'out' ? 'Cung cấp thiết bị văn phòng' : 'Vé máy bay công tác',
                '7814815',
                '8',
                '625185',
                '8440000',
                $today,
                $after30Days,
                $today,
                '1',
                'Đã thanh toán đủ',
            ],
            [
                '51568',
                $flow === 'out' ? 'Đại Lý Điện Máy Toàn Thắng' : 'Sơn Sài Gòn Hải Phòng',
                '0398765432',
                '0918333444',
                $flow === 'out' ? 'Xuất kho thiết bị đợt 1' : 'Sơn tường nội thất',
                '7450000',
                '8',
                '596000',
                '8046000',
                $today,
                $after30Days,
                $today,
                '1',
                'Đã thanh toán đủ',
            ],
            [
                '51569',
                $flow === 'out' ? 'Công Ty CP Đầu Tư Sao Mai' : 'Công Ty May Thêu Thuận Tiến',
                '0108889999',
                '0987654321',
                $flow === 'out' ? 'Dịch vụ tư vấn giải pháp' : 'May đồng phục nhân viên',
                '15000000',
                '10',
                '1500000',
                '0',
                $today,
                $after30Days,
                '',
                '1',
                'Chưa thanh toán',
            ],
        ];

        $fp = @fopen($outputPath, 'w');
        if (!$fp) {
            return false;
        }

        // Ghi BOM UTF-8 (\xEF\xBB\xBF) để Excel mở tiếng Việt chuẩn
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        return true;
    }

    /**
     * Tạo file mẫu Excel (.xlsx) với định dạng và hàng mẫu chuẩn
     */
    public function generateTemplateXlsx(string $outputPath, string $flow = 'in'): bool
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $headers = [
            'Số Hóa Đơn',
            'Tên Doanh Nghiệp / Đối Tác',
            'Mã Số Thuế',
            'Số Điện Thoại',
            'Nội Dung Diễn Giải',
            'Tiền Trước Thuế',
            'Thuế Suất VAT (%)',
            'Tiền Thuế VAT',
            'Đã Thanh Toán',
            'Ngày Lập (dd/mm/yyyy)',
            'Hạn Thanh Toán (dd/mm/yyyy)',
            'Ngày Thanh Toán (dd/mm/yyyy)',
            'Phân Loại (1=Quan tâm, 0=Không)',
            'Ghi Chú',
        ];

        $today = date('d/m/Y');
        $after30Days = date('d/m/Y', strtotime('+30 days'));
        $fiveDaysAgo = date('d/m/Y', strtotime('-5 days'));

        $sampleRows = [
            [
                '51567',
                $flow === 'out' ? 'Công Ty TNHH Đại Phát' : 'Bảo Gia Trần',
                '0312345678',
                '0901234567',
                $flow === 'out' ? 'Cung cấp thiết bị văn phòng' : 'Vé máy bay công tác',
                '7814815',
                '8',
                '625185',
                '8440000',
                $today,
                $after30Days,
                $today,
                '1', // 1: Quan tâm
                'Đã chuyển khoản đủ qua ngân hàng',
            ],
            [
                '51568',
                $flow === 'out' ? 'Đại Lý Điện Máy Toàn Thắng' : 'Sơn Sài Gòn Hải Phòng',
                '0398765432',
                '0918333444',
                $flow === 'out' ? 'Xuất kho thiết bị đợt 1' : 'Sơn tường nội thất',
                '7450000',
                '10',
                '745000',
                '5000000',
                $fiveDaysAgo,
                $after30Days,
                $today,
                '1', // 1: Quan tâm
                'Thanh toán đợt 1 (còn thiếu 3.195.000 đ)',
            ],
            [
                '', // Để trống để tự sinh số HĐ
                $flow === 'out' ? 'Khách hàng mới chưa có trong danh bạ' : 'Công ty Cung Ứng Vật Tư Mới',
                '0300112233',
                '0933666777',
                'Mua vật tư dự án (Hệ thống tự tạo doanh nghiệp mới nếu chưa có)',
                '1500000',
                '8',
                '', // Tự tính nếu để trống
                '0',
                $today,
                '',
                '',
                '0', // 0: Không quan tâm
                'Để trống số HĐ hệ thống tự động sinh số mới',
            ],
        ];

        // Tạo mảng chuỗi dùng chung (shared strings)
        $uniqueStrings = [];
        $stringIndex = [];
        $addString = function ($str) use (&$uniqueStrings, &$stringIndex) {
            $str = (string)$str;
            if (!isset($stringIndex[$str])) {
                $idx = count($uniqueStrings);
                $uniqueStrings[] = $str;
                $stringIndex[$str] = $idx;
            }
            return $stringIndex[$str];
        };

        foreach ($headers as $h) {
            $addString($h);
        }
        foreach ($sampleRows as $row) {
            foreach ($row as $val) {
                $addString($val);
            }
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><name val="Calibri"/><sz val="11"/></font>
        <font><b/><color rgb="FFFFFFFF"/><name val="Calibri"/><sz val="11"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF0284C7"/></patternFill></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
    </cellXfs>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="MauHoaDon" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // xl/sharedStrings.xml
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sstXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($uniqueStrings) . '" uniqueCount="' . count($uniqueStrings) . '">' . "\n";
        foreach ($uniqueStrings as $str) {
            $sstXml .= '    <si><t>' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>' . "\n";
        }
        $sstXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);

        // xl/worksheets/sheet1.xml
        $colLetter = function ($idx) {
            $letter = '';
            while ($idx >= 0) {
                $letter = chr($idx % 26 + 65) . $letter;
                $idx = intval($idx / 26) - 1;
            }
            return $letter;
        };

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";

        // Column widths
        $sheetXml .= '    <cols>' . "\n";
        $sheetXml .= '        <col min="1" max="1" width="16" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="2" max="2" width="32" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="3" max="3" width="16" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="4" max="4" width="16" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="5" max="5" width="35" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="6" max="6" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="7" max="7" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="8" max="8" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="9" max="9" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="10" max="10" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="11" max="11" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="12" max="12" width="18" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="13" max="13" width="22" customWidth="1"/>' . "\n";
        $sheetXml .= '        <col min="14" max="14" width="30" customWidth="1"/>' . "\n";
        $sheetXml .= '    </cols>' . "\n";

        $sheetXml .= '    <sheetData>' . "\n";

        // Dòng tiêu đề
        $sheetXml .= '        <row r="1">' . "\n";
        foreach ($headers as $colIdx => $h) {
            $cRef = $colLetter($colIdx) . '1';
            $sIdx = $stringIndex[$h];
            $sheetXml .= '            <c r="' . $cRef . '" t="s" s="1"><v>' . $sIdx . '</v></c>' . "\n";
        }
        $sheetXml .= '        </row>' . "\n";

        // Các dòng dữ liệu mẫu
        foreach ($sampleRows as $rIdx => $row) {
            $rowNum = $rIdx + 2;
            $sheetXml .= '        <row r="' . $rowNum . '">' . "\n";
            foreach ($row as $colIdx => $val) {
                $cRef = $colLetter($colIdx) . $rowNum;
                $sIdx = $stringIndex[$val];
                $sheetXml .= '            <c r="' . $cRef . '" t="s"><v>' . $sIdx . '</v></c>' . "\n";
            }
            $sheetXml .= '        </row>' . "\n";
        }

        $sheetXml .= '    </sheetData>' . "\n";
        $sheetXml .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();
        return true;
    }
}

