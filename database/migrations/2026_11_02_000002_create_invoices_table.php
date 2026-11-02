<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('debt_id')->nullable()->constrained('debts')->nullOnDelete();
            $table->string('type')->default('out'); // out (Hóa đơn đầu ra - Bán hàng), in (Hóa đơn đầu vào - Mua hàng)
            $table->string('title');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(10.00); // 0%, 8%, 10%
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('unpaid'); // unpaid (Chưa thanh toán), paid (Đã thanh toán), cancelled (Đã hủy)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
