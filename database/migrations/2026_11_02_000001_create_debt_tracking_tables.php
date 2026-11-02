<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('both'); // customer (họ nợ tôi), supplier (tôi nợ họ), both
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('type'); // receivable (Phải thu - Họ nợ tôi), payable (Phải trả - Tôi nợ họ)
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, partial, completed, overdue
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('transfer'); // transfer, cash, other
            $table->string('reference_code', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('companies');
    }
};
