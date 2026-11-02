<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DebtController;

// Mặc định chuyển hướng vào Hóa Đơn Đầu Vào
Route::get('/', function () {
    return redirect()->route('invoice.in');
});

// 1. Quản Lý Hóa Đơn Đầu Vào (Mua Hàng / Chi Phí)
Route::get('/hoa-don-dau-vao', [InvoiceController::class, 'inInvoices'])->name('invoice.in');

// 2. Quản Lý Hóa Đơn Xuất Ra (Bán Hàng / Doanh Thu)
Route::get('/hoa-don-xuat-ra', [InvoiceController::class, 'outInvoices'])->name('invoice.out');

// 3. Quản Lý Doanh Nghiệp
Route::get('/doanh-nghiep', [CompanyController::class, 'index'])->name('company.index');
Route::post('/companies', [CompanyController::class, 'store'])->name('company.store');
Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('company.update');
Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');

// Hành động Hóa đơn
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoice.store');
Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoice.update');
Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoice.pay');
Route::post('/invoices/{id}/toggle-status', [InvoiceController::class, 'toggleStatus'])->name('invoice.toggle-status');
Route::get('/hoa-don/{id}/in', [InvoiceController::class, 'print'])->name('invoice.print');

// Đối tác & Công nợ
Route::post('/partners', [DebtController::class, 'storePartner'])->name('partner.store');
Route::get('/cong-no', [DebtController::class, 'index'])->name('debt.index');
Route::post('/debts', [DebtController::class, 'storeDebt'])->name('debt.store');
Route::put('/debts/{id}', [DebtController::class, 'updateDebt'])->name('debt.update');
Route::delete('/debts/{id}', [DebtController::class, 'destroyDebt'])->name('debt.destroy');
Route::post('/debts/{id}/payments', [DebtController::class, 'addPayment'])->name('debt.payment.store');
Route::delete('/payments/{paymentId}', [DebtController::class, 'destroyPayment'])->name('debt.payment.destroy');
Route::get('/debts/{id}/statement', [DebtController::class, 'statement'])->name('debt.statement');
