<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Debt;
use App\Models\DebtPayment;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Doanh nghiệp (Người dùng đang quản lý các công ty)
        $c1 = Company::create([
            'name' => 'Công ty TNHH Cát Vượng',
            'code' => 'CV-CORP',
            'tax_code' => '0109887766',
            'phone' => '0912 345 678',
            'representative' => 'Nguyễn Thành Nam',
            'position' => 'Giám Đốc',
            'address' => 'Số 18, Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội',
            'notes' => 'Đơn vị phụ trách dịch vụ kỹ thuật & công nghệ',
        ]);

        $c2 = Company::create([
            'name' => 'Công ty Cổ Phần Nam Phát',
            'code' => 'NP-TRADE',
            'tax_code' => '0315668899',
            'phone' => '0988 765 432',
            'representative' => 'Trần Văn Phát',
            'position' => 'Tổng Giám Đốc',
            'address' => 'Tầng 5, Tòa Nhà Bitexco, Quận 1, TP. Hồ Chí Minh',
            'notes' => 'Đơn vị thương mại, phân phối thiết bị',
        ]);

        // 2. Đối tác / Khách hàng / Nhà cung cấp
        // Đối tác của Cát Vượng
        $p1 = Partner::create([
            'company_id' => $c1->id,
            'name' => 'Công ty Cổ Phần Xây Dựng Á Đông',
            'type' => 'customer',
            'phone' => '0903 111 222',
            'email' => 'adong.build@gmail.com',
            'address' => 'Cầu Giấy, Hà Nội',
            'notes' => 'Khách hàng dự án hệ thống tháng 10',
        ]);

        $p2 = Partner::create([
            'company_id' => $c1->id,
            'name' => 'Công ty TNHH Cơ Điện Minh Đức',
            'type' => 'supplier',
            'phone' => '0977 222 333',
            'email' => 'minhduc.electric@gmail.com',
            'address' => 'Thanh Xuân, Hà Nội',
            'notes' => 'Nhà cung cấp vật tư bo mạch và linh kiện',
        ]);

        $p3 = Partner::create([
            'company_id' => $c1->id,
            'name' => 'Đại Lý Điện Máy Toàn Thắng',
            'type' => 'customer',
            'phone' => '0918 333 444',
            'email' => 'toanthang.shop@gmail.com',
            'address' => 'Hai Bà Trưng, Hà Nội',
            'notes' => 'Lấy hàng định kỳ hàng tháng',
        ]);

        // Đối tác của Nam Phát
        $p4 = Partner::create([
            'company_id' => $c2->id,
            'name' => 'Tập Đoàn Bất Động Sản Hưng Gia',
            'type' => 'customer',
            'phone' => '0944 555 666',
            'email' => 'hunggia.group@gmail.com',
            'address' => 'Quận 1, TP.HCM',
            'notes' => 'Dự án cung cấp thiết bị văn phòng',
        ]);

        $p5 = Partner::create([
            'company_id' => $c2->id,
            'name' => 'Tổng Kho Linh Kiện Miền Nam',
            'type' => 'supplier',
            'phone' => '0933 666 777',
            'email' => 'tongkho.miennam@gmail.com',
            'address' => 'Bình Tân, TP.HCM',
            'notes' => 'Nhập lô hàng linh kiện máy móc',
        ]);

        // 3. Khoản công nợ mẫu
        // Cát Vượng: Họ nợ tôi (Phải thu - Receivable)
        $d1 = Debt::create([
            'code' => 'CN-THU-001',
            'company_id' => $c1->id,
            'partner_id' => $p1->id,
            'type' => 'receivable',
            'title' => 'Thi công bảo trì hệ thống điều khiển Quý 3',
            'amount' => 45000000,
            'paid_amount' => 20000000,
            'issue_date' => Carbon::now()->subDays(25)->toDateString(),
            'due_date' => Carbon::now()->addDays(10)->toDateString(),
            'status' => 'partial',
            'notes' => 'Đã ứng đợt 1 là 20.000.000đ, còn lại nghiệm thu xong thanh toán',
        ]);

        DebtPayment::create([
            'debt_id' => $d1->id,
            'payment_date' => Carbon::now()->subDays(20)->toDateString(),
            'amount' => 20000000,
            'payment_method' => 'transfer',
            'reference_code' => 'FT2628491029',
            'notes' => 'Tạm ứng 20 triệu qua Vietcombank',
        ]);

        // Cát Vượng: Họ nợ tôi (Chưa trả đồng nào - Sắp đến hạn)
        $d2 = Debt::create([
            'code' => 'CN-THU-002',
            'company_id' => $c1->id,
            'partner_id' => $p3->id,
            'type' => 'receivable',
            'title' => 'Cung cấp 10 bộ điều khiển biến tần',
            'amount' => 28500000,
            'paid_amount' => 0,
            'issue_date' => Carbon::now()->subDays(12)->toDateString(),
            'due_date' => Carbon::now()->addDays(3)->toDateString(),
            'status' => 'pending',
            'notes' => 'Hẹn thanh toán vào thứ 6 tuần này',
        ]);

        // Cát Vượng: Tôi nợ họ (Phải trả - Payable)
        $d3 = Debt::create([
            'code' => 'CN-TRA-001',
            'company_id' => $c1->id,
            'partner_id' => $p2->id,
            'type' => 'payable',
            'title' => 'Tiền nhập lô linh kiện cảm biến nhiệt & role',
            'amount' => 18200000,
            'paid_amount' => 8000000,
            'issue_date' => Carbon::now()->subDays(15)->toDateString(),
            'due_date' => Carbon::now()->addDays(5)->toDateString(),
            'status' => 'partial',
            'notes' => 'Đã chuyển khoản trước 8 triệu, còn 10.2 triệu',
        ]);

        DebtPayment::create([
            'debt_id' => $d3->id,
            'payment_date' => Carbon::now()->subDays(14)->toDateString(),
            'amount' => 8000000,
            'payment_method' => 'transfer',
            'reference_code' => 'MB8847192',
            'notes' => 'Chuyển khoản cọc linh kiện',
        ]);

        // Nam Phát: Họ nợ tôi (Receivable)
        $d4 = Debt::create([
            'code' => 'CN-THU-003',
            'company_id' => $c2->id,
            'partner_id' => $p4->id,
            'type' => 'receivable',
            'title' => 'Cung cấp trọn gói vật tư tòa nhà Hưng Gia Tower',
            'amount' => 120000000,
            'paid_amount' => 50000000,
            'issue_date' => Carbon::now()->subDays(40)->toDateString(),
            'due_date' => Carbon::now()->subDays(5)->toDateString(),
            'status' => 'overdue',
            'notes' => 'Đã quá hạn 5 ngày, kế toán đang liên hệ giục thanh toán đợt 2',
        ]);

        DebtPayment::create([
            'debt_id' => $d4->id,
            'payment_date' => Carbon::now()->subDays(35)->toDateString(),
            'amount' => 50000000,
            'payment_method' => 'transfer',
            'reference_code' => 'VCB99281920',
            'notes' => 'Tạm ứng hợp đồng đợt 1',
        ]);

        // Nam Phát: Khoản Phải trả (Payable)
        $d5 = Debt::create([
            'code' => 'CN-TRA-002',
            'company_id' => $c2->id,
            'partner_id' => $p5->id,
            'type' => 'payable',
            'title' => 'Tiền hàng nhập kho tháng 10 đợt 2',
            'amount' => 35000000,
            'paid_amount' => 0,
            'issue_date' => Carbon::now()->subDays(10)->toDateString(),
            'due_date' => Carbon::now()->addDays(15)->toDateString(),
            'status' => 'pending',
            'notes' => 'Được gối đầu công nợ 25 ngày',
        ]);

        // 4. Hóa đơn (Invoices) mẫu cho từng công ty
        // Cát Vượng: Hóa đơn đầu ra (Bán hàng)
        \App\Models\Invoice::create([
            'invoice_number' => 'HD-CV-26-001',
            'company_id' => $c1->id,
            'partner_id' => $p1->id,
            'debt_id' => $d1->id,
            'type' => 'out',
            'title' => 'Hóa đơn dịch vụ bảo trì hệ thống điều khiển Quý 3',
            'subtotal' => 41666667,
            'tax_rate' => 8.00,
            'tax_amount' => 3333333,
            'total_amount' => 45000000,
            'issue_date' => Carbon::now()->subDays(25)->toDateString(),
            'due_date' => Carbon::now()->addDays(10)->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Xuất theo hợp đồng số CV-2026-08',
        ]);

        // Cát Vượng: Hóa đơn đầu vào (Mua vật tư)
        \App\Models\Invoice::create([
            'invoice_number' => 'HD-CV-26-002',
            'company_id' => $c1->id,
            'partner_id' => $p2->id,
            'debt_id' => $d3->id,
            'type' => 'in',
            'title' => 'Hóa đơn linh kiện cảm biến nhiệt & role điều khiển',
            'subtotal' => 16545455,
            'tax_rate' => 10.00,
            'tax_amount' => 1654545,
            'total_amount' => 18200000,
            'issue_date' => Carbon::now()->subDays(15)->toDateString(),
            'due_date' => Carbon::now()->addDays(5)->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Đã nhận đủ hàng và phiếu xuất kho',
        ]);

        // Nam Phát: Hóa đơn đầu ra (Bán thiết bị văn phòng)
        \App\Models\Invoice::create([
            'invoice_number' => 'HD-NP-26-001',
            'company_id' => $c2->id,
            'partner_id' => $p4->id,
            'debt_id' => $d4->id,
            'type' => 'out',
            'title' => 'Hóa đơn cung cấp trọn gói thiết bị Hưng Gia Tower',
            'subtotal' => 109090909,
            'tax_rate' => 10.00,
            'tax_amount' => 10909091,
            'total_amount' => 120000000,
            'issue_date' => Carbon::now()->subDays(40)->toDateString(),
            'due_date' => Carbon::now()->subDays(5)->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Khách hàng hẹn thanh toán phần còn lại sau đối soát',
        ]);

        // Nam Phát: Hóa đơn đầu vào (Nhập kho linh kiện)
        \App\Models\Invoice::create([
            'invoice_number' => 'HD-NP-26-002',
            'company_id' => $c2->id,
            'partner_id' => $p5->id,
            'debt_id' => $d5->id,
            'type' => 'in',
            'title' => 'Hóa đơn nhập linh kiện máy móc đợt 2 tháng 10',
            'subtotal' => 31818182,
            'tax_rate' => 10.00,
            'tax_amount' => 3181818,
            'total_amount' => 35000000,
            'issue_date' => Carbon::now()->subDays(10)->toDateString(),
            'due_date' => Carbon::now()->addDays(15)->toDateString(),
            'status' => 'unpaid',
            'notes' => 'Hạn thanh toán 25 ngày theo hợp đồng nguyên tắc',
        ]);
    }
}
