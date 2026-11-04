<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Auth Routes (Dành cho khách chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Các chức năng hệ thống (Bảo vệ bởi Auth middleware)
Route::middleware('auth')->group(function () {
    // Mặc định chuyển hướng vào Hóa Đơn Đầu Vào
    Route::get('/', function () {
        return redirect()->route('invoice.in');
    });

    // 1. Quản Lý Hóa Đơn Đầu Vào (Mua Hàng / Chi Phí)
    Route::get('/hoa-don-dau-vao', [InvoiceController::class, 'inInvoices'])->name('invoice.in');

    // 2. Quản Lý Hóa Đơn Xuất Ra (Bán Hàng / Doanh Thu)
    Route::get('/hoa-don-xuat-ra', [InvoiceController::class, 'outInvoices'])->name('invoice.out');

    // 3. Quản Lý Doanh Nghiệp (Nhà Cung Cấp / Đối Tác)
    Route::get('/doanh-nghiep', [CompanyController::class, 'index'])->name('company.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('company.store');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('company.update');
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');

    // Đính kèm file Doanh nghiệp (Hồ sơ, ĐKKD, HĐ Nguyên Tắc, Catalogue, Báo giá...)
    Route::get('/partners/{id}/attachments', [CompanyController::class, 'getAttachments'])->name('partner.attachments.index');
    Route::post('/partners/{id}/attachments', [CompanyController::class, 'uploadAttachments'])->name('partner.attachments.store');
    Route::get('/partner-attachments/{id}/view', [CompanyController::class, 'viewAttachment'])->name('partner.attachments.view');
    Route::get('/partner-attachments/{id}/download', [CompanyController::class, 'downloadAttachment'])->name('partner.attachments.download');
    Route::delete('/partner-attachments/{id}', [CompanyController::class, 'deleteAttachment'])->name('partner.attachments.destroy');

    // Quản lý CTY của tôi (Dành riêng cho Admin)
    Route::get('/quan-ly-cty', [CompanyController::class, 'myCompaniesIndex'])->name('my-company.index');
    Route::post('/my-companies', [CompanyController::class, 'storeMyCompany'])->name('my-company.store');
    Route::put('/my-companies/{id}', [CompanyController::class, 'updateMyCompany'])->name('my-company.update');
    Route::delete('/my-companies/{id}', [CompanyController::class, 'destroyMyCompany'])->name('my-company.destroy');

    // Quản lý tài khoản cho từng CTY
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Hành động Hóa đơn
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoice.pay');
    Route::post('/invoices/{id}/toggle-status', [InvoiceController::class, 'toggleStatus'])->name('invoice.toggle-status');
    Route::post('/invoices/{id}/toggle-concern', [InvoiceController::class, 'toggleConcern'])->name('invoice.toggle-concern');
    Route::get('/hoa-don/{id}/in', [InvoiceController::class, 'print'])->name('invoice.print');

    // Đính kèm file hóa đơn (HĐ, BG, HS liên quan, Hóa đơn)
    Route::get('/invoices/{id}/attachments', [InvoiceController::class, 'getAttachments'])->name('invoice.attachments.index');
    Route::post('/invoices/{id}/attachments', [InvoiceController::class, 'uploadAttachments'])->name('invoice.attachments.store');
    Route::get('/invoice-attachments/{id}/view', [InvoiceController::class, 'viewAttachment'])->name('invoice.attachments.view');
    Route::get('/invoice-attachments/{id}/download', [InvoiceController::class, 'downloadAttachment'])->name('invoice.attachments.download');
    Route::delete('/invoice-attachments/{id}', [InvoiceController::class, 'deleteAttachment'])->name('invoice.attachments.destroy');

    // Đối tác & Công nợ
    Route::post('/partners', [DebtController::class, 'storePartner'])->name('partner.store');
    Route::get('/cong-no', [DebtController::class, 'index'])->name('debt.index');
    Route::post('/debts', [DebtController::class, 'storeDebt'])->name('debt.store');
    Route::put('/debts/{id}', [DebtController::class, 'updateDebt'])->name('debt.update');
    Route::delete('/debts/{id}', [DebtController::class, 'destroyDebt'])->name('debt.destroy');
    Route::post('/debts/{id}/payments', [DebtController::class, 'addPayment'])->name('debt.payment.store');
    Route::delete('/payments/{paymentId}', [DebtController::class, 'destroyPayment'])->name('debt.payment.destroy');
    Route::get('/debts/{id}/statement', [DebtController::class, 'statement'])->name('debt.statement');
});
