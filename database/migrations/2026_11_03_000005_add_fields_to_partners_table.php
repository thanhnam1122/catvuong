<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('tax_code', 50)->nullable()->after('name');
            $table->string('code', 50)->nullable()->after('tax_code');
            $table->string('representative', 100)->nullable()->after('phone');
            $table->string('position', 100)->nullable()->after('representative');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['tax_code', 'code', 'representative', 'position']);
        });
    }
};

