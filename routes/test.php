<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Filament\Resources\TaxRateResource\Pages\ManageTaxRates;
use Modules\Core\Filament\Resources\UserResource\Pages\ManageUsers;
use Modules\Expenses\Filament\Resources\ExpenseCategoryResource\Pages\ManageExpenseCategories;
use Modules\Expenses\Filament\Resources\ExpenseResource\Pages\ManageExpenses;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource\Pages\ManageExpenseVendors;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages\ManageInvoiceGroups;
use Modules\Invoices\Filament\Resources\InvoiceResource\Pages\ManageInvoices;
use Modules\Payments\Filament\Resources\PaymentMethodResource\Pages\ManagePaymentMethods;
use Modules\Payments\Filament\Resources\PaymentResource\Pages\ManagePayments;
use Modules\Products\Filament\Resources\ProductFamilyResource\Pages\ManageProductFamilies;
use Modules\Products\Filament\Resources\ProductResource\Pages\ManageProducts;
use Modules\Products\Filament\Resources\ProductUnitResource\Pages\ManageProductUnits;
use Modules\Projects\Filament\Resources\ProjectResource\Pages\ManageProjects;
use Modules\Projects\Filament\Resources\TaskResource\Pages\ManageTasks;
use Modules\Quotes\Filament\Resources\QuoteResource\Pages\ManageQuotes;

Route::prefix('ivpl')->group(function (): void {
    // Clients
    /*Route::prefix('clients')->name('filament.ivpl.resources.clients.')->group(function (): void {
        Route::post('/', [ManageClients::class, 'store'])->name('store');
        Route::put('/{record}', [ManageClients::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageClients::class, 'destroy'])->name('destroy');
    });*/

    // Expense Categories
    Route::prefix('expense-categories')->name('filament.ivpl.resources.expense-categories.')->group(function (): void {
        Route::post('/', [ManageExpenseCategories::class, 'store'])->name('store');
        Route::put('/{record}', [ManageExpenseCategories::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageExpenseCategories::class, 'destroy'])->name('destroy');
    });

    // Expenses
    Route::prefix('expenses')->name('filament.ivpl.resources.expenses.')->group(function (): void {
        Route::post('/', [ManageExpenses::class, 'store'])->name('store');
        Route::put('/{record}', [ManageExpenses::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageExpenses::class, 'destroy'])->name('destroy');
    });

    // Expense Vendors
    Route::prefix('expense-vendors')->name('filament.ivpl.resources.expense-vendors.')->group(function (): void {
        Route::post('/', [ManageExpenseVendors::class, 'store'])->name('store');
        Route::put('/{record}', [ManageExpenseVendors::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageExpenseVendors::class, 'destroy'])->name('destroy');
    });

    // Invoice Groups
    Route::prefix('invoice-groups')->name('filament.ivpl.resources.invoice-groups.')->group(function (): void {
        Route::post('/', [ManageInvoiceGroups::class, 'store'])->name('store');
        Route::put('/{record}', [ManageInvoiceGroups::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageInvoiceGroups::class, 'destroy'])->name('destroy');
    });

    // Invoices
    Route::prefix('invoices')->name('filament.ivpl.resources.invoices.')->group(function (): void {
        Route::post('/', [ManageInvoices::class, 'store'])->name('store');
        Route::put('/{record}', [ManageInvoices::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageInvoices::class, 'destroy'])->name('destroy');
    });

    // Payment Methods
    Route::prefix('payment-methods')->name('filament.ivpl.resources.payment-methods.')->group(function (): void {
        Route::post('/', [ManagePaymentMethods::class, 'store'])->name('store');
        Route::put('/{record}', [ManagePaymentMethods::class, 'update'])->name('update');
        Route::delete('/{record}', [ManagePaymentMethods::class, 'destroy'])->name('destroy');
    });

    // Payments
    Route::prefix('payments')->name('filament.ivpl.resources.payments.')->group(function (): void {
        Route::post('/', [ManagePayments::class, 'store'])->name('store');
        Route::put('/{record}', [ManagePayments::class, 'update'])->name('update');
        Route::delete('/{record}', [ManagePayments::class, 'destroy'])->name('destroy');
    });

    // Product Families
    Route::prefix('product-families')->name('filament.ivpl.resources.product-families.')->group(function (): void {
        Route::post('/', [ManageProductFamilies::class, 'store'])->name('store');
        Route::put('/{record}', [ManageProductFamilies::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageProductFamilies::class, 'destroy'])->name('destroy');
    });

    // Products
    Route::prefix('products')->name('filament.ivpl.resources.products.')->group(function (): void {
        Route::post('/', [ManageProducts::class, 'store'])->name('store');
        Route::put('/{record}', [ManageProducts::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageProducts::class, 'destroy'])->name('destroy');
    });

    // Product Units
    Route::prefix('product-units')->name('filament.ivpl.resources.product-units.')->group(function (): void {
        Route::post('/', [ManageProductUnits::class, 'store'])->name('store');
        Route::put('/{record}', [ManageProductUnits::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageProductUnits::class, 'destroy'])->name('destroy');
    });

    // Projects
    Route::prefix('projects')->name('filament.ivpl.resources.projects.')->group(function (): void {
        Route::post('/', [ManageProjects::class, 'store'])->name('store');
        Route::put('/{record}', [ManageProjects::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageProjects::class, 'destroy'])->name('destroy');
    });

    // Quotes
    Route::prefix('quotes')->name('filament.ivpl.resources.quotes.')->group(function (): void {
        Route::post('/', [ManageQuotes::class, 'store'])->name('store');
        Route::put('/{record}', [ManageQuotes::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageQuotes::class, 'destroy'])->name('destroy');
    });

    // Tasks
    Route::prefix('tasks')->name('filament.ivpl.resources.tasks.')->group(function (): void {
        Route::post('/', [ManageTasks::class, 'store'])->name('store');
        Route::put('/{record}', [ManageTasks::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageTasks::class, 'destroy'])->name('destroy');
    });

    // Tax Rates
    Route::prefix('tax-rates')->name('filament.ivpl.resources.tax-rates.')->group(function (): void {
        Route::post('/', [ManageTaxRates::class, 'store'])->name('store');
        Route::put('/{record}', [ManageTaxRates::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageTaxRates::class, 'destroy'])->name('destroy');
    });

    // Users
    Route::prefix('users')->name('filament.ivpl.resources.users.')->group(function (): void {
        Route::post('/', [ManageUsers::class, 'store'])->name('store');
        Route::put('/{record}', [ManageUsers::class, 'update'])->name('update');
        Route::delete('/{record}', [ManageUsers::class, 'destroy'])->name('destroy');
    });
});
