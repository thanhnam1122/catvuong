<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TechSpecController;

Route::get('/', function () {
    return view('quotation');
});

Route::get('/bao-gia', function () {
    return view('quotation');
});

// Resource routes cho Khách hàng, Sản phẩm & Thông số kỹ thuật trang 2
Route::resource('khach-hang', CustomerController::class)->names('customers');
Route::resource('san-pham', ProductController::class)->names('products');
Route::resource('thong-so-trang-2', TechSpecController::class)->names('tech-specs');

// API Endpoints lấy dữ liệu cho Form báo giá
Route::get('/api/customers', [CustomerController::class, 'apiList'])->name('api.customers');
Route::get('/api/products', [ProductController::class, 'apiList'])->name('api.products');
Route::get('/api/tech-specs', [TechSpecController::class, 'apiList'])->name('api.tech_specs');

