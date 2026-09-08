<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable(); // Mã sản phẩm / Linh kiện
            $table->string('brand')->nullable(); // Hãng SX / 品牌
            $table->string('model')->nullable(); // Model / 型號
            $table->text('description')->nullable(); // Diễn giải / 說明
            $table->string('unit')->default('Cái'); // Đơn vị tính
            $table->decimal('price', 15, 2)->default(0); // Đơn giá / 單價
            $table->string('warranty_period')->nullable(); // Thời gian bảo hành
            $table->text('specs')->nullable(); // Thông số kỹ thuật
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
