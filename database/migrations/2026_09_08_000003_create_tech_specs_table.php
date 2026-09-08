<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_specs', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable(); // Hãng Cân / Thương Hiệu
            $table->string('model')->nullable(); // Model / Tải Trọng
            $table->string('image_path')->nullable(); // Đường dẫn hình ảnh phụ kiện
            $table->text('specs')->nullable(); // Thông số kỹ thuật và Tính năng
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_specs');
    }
};
