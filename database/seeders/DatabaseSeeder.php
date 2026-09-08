<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Initial Customer seed
        if (Customer::count() === 0) {
            Customer::create([
                'code' => 'TUICO03',
                'name' => 'Công Ty Cổ Phần TuiCo 3',
                'contact_person' => 'Chị Hằng (thu mua) 0989 169170 , Chị Vân',
                'tel' => '02513 671222',
                'fax' => '02513 671666, 02513 671345',
                'address' => 'Lô đất số 1-16, KCN Hố Nai, Phường Hố Nai, Thành Phố Đồng Nai, Việt Nam',
                'social_contact' => 'Zalo, MinhHangCao, hang@tuico.com',
            ]);

            Customer::create([
                'code' => 'KHACH02',
                'name' => 'Công Ty TNHH Sản Xuất Thực Phẩm Á Châu',
                'contact_person' => 'Anh Minh (Kỹ thuật) 0912 345 678',
                'tel' => '02838 123456',
                'fax' => '02838 654321',
                'address' => 'KCN Tân Bình, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh',
                'social_contact' => 'Zalo, AnhMinh, minh.achau@gmail.com',
            ]);
        }

        // Initial Product seed
        if (Product::count() === 0) {
            Product::create([
                'code' => 'JWI-3100',
                'brand' => 'Jadever',
                'model' => 'JWI-3100',
                'description' => "cân điện tử 150kg/10g (230050201)\n- Hư nguồn (BH 01 tháng), hiệu chuẩn cân (bị sai kg)",
                'unit' => 'Cái',
                'price' => 750000,
                'warranty_period' => '01 tháng',
                'specs' => 'Đầu hiển thị cân bàn Jadever JWI-3100 vỏ inox chống nước',
            ]);

            Product::create([
                'code' => 'DISP-SHIMADZU',
                'brand' => 'Shimadzu Nhật',
                'model' => 'Display UX/UW UP-X/UP-Y',
                'description' => 'Màn hình hiển thị số LCD cân phân tích Shimadzu UX/UW',
                'unit' => 'Màn hình',
                'price' => 1200000,
                'warranty_period' => '12 tháng',
                'specs' => "- Display LCD (màn hình hiển thị số)\nBảo hành 12 tháng cho lỗi kỹ thuật như: Mất nét, mất số, mờ số\nTrường hợp không bảo hành: Bị cấn, rơi rớt, hóa chất lau màn hình.",
            ]);

            Product::create([
                'code' => 'LOADCELL-500KG',
                'brand' => 'Mettler Toledo',
                'model' => 'Loadcell S-Beam 500kg',
                'description' => 'Cảm biến lực LoadCell cân bàn điện tử 500kg',
                'unit' => 'Bộ',
                'price' => 1850000,
                'warranty_period' => '12 tháng',
                'specs' => 'Cảm biến lực thép hợp kim chống mài mòn, đạt chuẩn IP67',
            ]);
        }

        // Initial TechSpec Trang 2 seed
        if (\App\Models\TechSpec::count() === 0) {
            \App\Models\TechSpec::create([
                'brand' => 'Shimadzu Nhật',
                'model' => 'Display UX/UW UP-X/UP-Y',
                'specs' => "- Display LCD (màn hình hiển thị số)\nBảo hành 12 tháng cho lỗi kỹ thuật như:\n- Mất nét, mất số, mờ số\nTrường hợp không bảo hành do lỗi người dùng:\n- Bị cấn, rơi rớt, nứt, bể màn hình display.\n- Dùng hóa chất lau màn hình dislay\n- Board mạch bị dính chất lỏng, hóa chất làm hư màn hình display",
            ]);
        }
    }
}
