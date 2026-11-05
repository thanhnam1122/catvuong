<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Gỡ bỏ unique toàn cục trên invoice_number để cho phép nhiều công ty/đối tác có số hóa đơn giống nhau (ví dụ: số 1, 0000001...)
        try {
            DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_invoice_number_unique;');
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique('invoice_number', 'invoices_invoice_number_unique');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};

