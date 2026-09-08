<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // Mã khách hàng
            $table->string('name'); // Kính gửi / Tên công ty
            $table->string('contact_person')->nullable(); // Người liên hệ
            $table->string('tel')->nullable(); // Điện thoại
            $table->string('fax')->nullable(); // Fax
            $table->text('address')->nullable(); // Địa chỉ
            $table->string('social_contact')->nullable(); // Mail, Teams, Zalo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
