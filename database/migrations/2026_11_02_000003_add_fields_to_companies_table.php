<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('tax_code', 50)->nullable()->after('name'); // Mã số thuế (MST)
            $table->string('representative', 100)->nullable()->after('phone'); // Người đại diện
            $table->string('position', 100)->nullable()->after('representative'); // Chức vụ
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tax_code', 'representative', 'position']);
        });
    }
};
