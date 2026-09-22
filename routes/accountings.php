<?php

use App\Http\Controllers\Accountings\Dashboard\DashboardController;
use App\Http\Controllers\Accountings\Distributors\DistributorsController;
use App\Http\Controllers\Accountings\Distributors\EnterprisesController as EnterprisesDistributorsController;
use App\Http\Controllers\Accountings\Distributors\InvoicesController as DistributorsInvoicesController;
use App\Http\Controllers\Accountings\Distributors\OrdersController as DistributorsOrdersController;
use App\Http\Controllers\Accountings\Enterprises\EnterprisesController;
use App\Http\Controllers\Accountings\Enterprises\EnterprisesOrdersController;
use App\Http\Controllers\Accountings\Invoices\GenerateController as InvoicesGenerateController;
use App\Http\Controllers\Accountings\Invoices\InvoicesController;
use App\Http\Controllers\Accountings\Invoices\ReportController as InvoicesReportController;
use App\Http\Controllers\Accountings\Orders\OrdersController;
use App\Http\Controllers\Accountings\Orders\ReportController as OrdersReportController;
use App\Http\Controllers\Accountings\Orders\ResumenController as OrdersResumenController;
use App\Http\Controllers\Accountings\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'accounting', 'middleware' => ['auth', 'accounting', 'session', 'panel.permission']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('accounting.dashboard');

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('accounting.profile');
        Route::post('/update', [SettingsController::class, 'update'])->name('accounting.profile.update');
    });

    Route::group(['prefix' => 'enterprises'], function () {
        Route::get('/view/{slack}', [EnterprisesController::class, 'view'])->name('accounting.enterprises.view');
        Route::get('/orders/{slack}', [EnterprisesOrdersController::class, 'index'])->name('accounting.enterprises.orders');
    });

    Route::group(['prefix' => 'distributors'], function () {
        Route::get('/', [DistributorsController::class, 'index'])->name('accounting.distributors');
        Route::get('/view/{slack}', [DistributorsController::class, 'view'])->name('accounting.distributors.view');
        Route::get('/invoices/{slack}', [DistributorsInvoicesController::class, 'index'])->name('accounting.distributors.invoices');
        Route::get('/orders/{slack}', [DistributorsOrdersController::class, 'index'])->name('accounting.distributors.orders');

        Route::get('/enterprises/{slack}', [EnterprisesDistributorsController::class, 'index'])->name('accounting.distributors.enterprises');

    });

    Route::group(['prefix' => 'orders'], function () {

        Route::get('/', [OrdersController::class, 'index'])->name('accounting.orders');
        Route::post('/update', [OrdersController::class, 'update'])->name('accounting.orders.update');
        Route::get('/print/{slack}', [OrdersController::class, 'print'])->name('accounting.orders.print');
        Route::get('/edit/{slack}', [OrdersController::class, 'edit'])->name('accounting.orders.edit');
        Route::get('/view/{slack}', [OrdersController::class, 'view'])->name('accounting.orders.view');

        Route::get('/report', [OrdersReportController::class, 'report'])->name('accounting.orders.report');
        Route::get('/report/generate', [OrdersReportController::class, 'generate'])->name('accounting.orders.generate');
        Route::post('/report/get/enterprises', [OrdersReportController::class, 'getEnterprises'])->name('accounting.orders.get.enterprises');

        Route::get('/resumen', [OrdersResumenController::class, 'resumen'])->name('accounting.orders.resumen');
        Route::get('/resumen/generate', [OrdersResumenController::class, 'generate'])->name('accounting.orders.resumen.generate');
        Route::post('/resumen/get/enterprises', [OrdersResumenController::class, 'getEnterprises'])->name('accounting.orders.resumen.get.enterprises');

    });

    Route::group(['prefix' => 'invoices'], function () {

        Route::get('/', [InvoicesController::class, 'index'])->name('accounting.invoices');
        Route::get('/create', [InvoicesController::class, 'create'])->name('accounting.invoices.create');
        Route::post('/store', [InvoicesController::class, 'store'])->name('accounting.invoices.store');
        Route::post('/update', [InvoicesController::class, 'update'])->name('accounting.invoices.update');
        Route::get('/edit/{slack}', [InvoicesController::class, 'edit'])->name('accounting.invoices.edit');
        Route::get('/view/{slack}', [InvoicesController::class, 'view'])->name('accounting.invoices.view');
        Route::get('/details/{slack}', [InvoicesController::class, 'details'])->name('accounting.invoices.details');

        Route::get('/report', [InvoicesReportController::class, 'report'])->name('accounting.invoices.report');
        Route::get('/report/generate', [InvoicesReportController::class, 'generate'])->name('accounting.invoices.generate');
        Route::get('/pdf/{slack}', [InvoicesGenerateController::class, 'generate'])->name('accounting.invoices.pdf');

    });

});
