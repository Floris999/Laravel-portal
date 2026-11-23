<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialExpenseController;
use App\Http\Controllers\FinancialInvoiceController;
use App\Http\Controllers\FinancialRegistrationController;
use App\Http\Controllers\FinancialUninvoicedController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('customers/archived', [CustomerController::class, 'archived'])->name('customers.archived');
    Route::patch('customers/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore')->withTrashed();
    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::get('suppliers/archived', [SupplierController::class, 'archived'])->name('suppliers.archived');
    Route::patch('suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore')->withTrashed();
    Route::resource('suppliers', SupplierController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::middleware('can:manage-finances')->group(function () {
        Route::get('financial/registration', [FinancialRegistrationController::class, 'index'])->name('financial.registration');
        Route::post('financial/registration', [FinancialRegistrationController::class, 'store'])->name('financial.registration.store');
        Route::get('financial/registration/{registration}/edit', [FinancialRegistrationController::class, 'edit'])->name('financial.registration.edit');
        Route::put('financial/registration/{registration}', [FinancialRegistrationController::class, 'update'])->name('financial.registration.update');
        Route::delete('financial/registration/{registration}', [FinancialRegistrationController::class, 'destroy'])->name('financial.registration.destroy');
        Route::get('financial/expenses', [FinancialExpenseController::class, 'index'])->name('financial.expenses');
        Route::post('financial/expenses', [FinancialExpenseController::class, 'store'])->name('financial.expenses.store');
        Route::get('financial/expenses/{expense}/edit', [FinancialExpenseController::class, 'edit'])->name('financial.expenses.edit');
        Route::put('financial/expenses/{expense}', [FinancialExpenseController::class, 'update'])->name('financial.expenses.update');
        Route::delete('financial/expenses/{expense}', [FinancialExpenseController::class, 'destroy'])->name('financial.expenses.destroy');
        Route::get('financial/uninvoiced', [FinancialUninvoicedController::class, 'index'])->name('financial.uninvoiced');
        Route::get('financial/uninvoiced/{customer}', [FinancialUninvoicedController::class, 'show'])->name('financial.uninvoiced.show');
        Route::get('financial/invoices', [FinancialInvoiceController::class, 'index'])->name('financial.invoices');
        Route::get('financial/invoices/{invoice}', [FinancialInvoiceController::class, 'show'])->name('financial.invoices.show');
    });
});

require __DIR__ . '/settings.php';
