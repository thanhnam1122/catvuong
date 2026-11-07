<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('note')->nullable(); // Ví dụ: Chuyển khoản đợt 1, Bù tiền cho HĐ cũ...
            $table->string('payment_method', 50)->default('transfer'); // transfer (Chuyển khoản), cash (Tiền mặt), other
            $table->string('reference_code', 100)->nullable(); // Mã giao dịch / số UNC
            $table->timestamps();
        });

        // Tự động tạo bản ghi lịch sử ban đầu cho các hóa đơn đã từng ghi nhận paid_amount > 0
        if (Schema::hasTable('invoices')) {
            $existingInvoices = \Illuminate\Support\Facades\DB::table('invoices')
                ->where('paid_amount', '>', 0)
                ->get();

            foreach ($existingInvoices as $inv) {
                \Illuminate\Support\Facades\DB::table('invoice_payments')->insert([
                    'invoice_id' => $inv->id,
                    'amount' => $inv->paid_amount,
                    'payment_date' => $inv->paid_date ?? ($inv->issue_date ?? date('Y-m-d')),
                    'note' => 'Thanh toán ban đầu',
                    'payment_method' => 'transfer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};

