<?php

use App\Http\Controllers\Distributors\Dashboard\DashboardController;
use App\Http\Controllers\Distributors\Enterprises\CourseController as EnterpriseCourseController;
use App\Http\Controllers\Distributors\Enterprises\EnterprisesController;
use App\Http\Controllers\Distributors\Enterprises\InscriptionsController as EnterpriseInscriptionsController;
use App\Http\Controllers\Distributors\Enterprises\ReassignController as EnterpriseReassignController;
use App\Http\Controllers\Distributors\Enterprises\StaffController as EnterpriseStaffController;
use App\Http\Controllers\Distributors\Enterprises\UserController as EnterpriseUserController;
use App\Http\Controllers\Distributors\Inscriptions\InscriptionsController;
use App\Http\Controllers\Distributors\Inscriptions\InscriptionsMassivesController;
use App\Http\Controllers\Distributors\Invoices\InvoicesController;
use App\Http\Controllers\Distributors\Orders\OrdersController;
use App\Http\Controllers\Distributors\Orders\ReportController as OrdersReportController;
use App\Http\Controllers\Distributors\Orders\ResumenController as OrdersResumenController;
use App\Http\Controllers\Distributors\Registers\RegistersController;
use App\Http\Controllers\Distributors\Settings\SettingsController;
use App\Http\Controllers\Distributors\Users\CertificatesController;
use App\Http\Controllers\Distributors\Users\ResultsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'distributor', 'middleware' => ['auth', 'distributor', 'panel.permission']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('distributor.dashboard');

    Route::group(['prefix' => 'settings'], function () {

        Route::get('/distributor', [SettingsController::class, 'distributor'])->name('distributor.settings.distributor');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('distributor.settings.profile');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('distributor.settings.notifications');
        Route::post('/distributor/update', [SettingsController::class, 'updateDistributor'])->name('distributor.settings.distributor.update');
        Route::post('/profile/update', [SettingsController::class, 'updateUser'])->name('distributor.settings.profile.update');
        Route::post('/notifications/update', [SettingsController::class, 'updateNotifications'])->name('distributor.settings.notifications.update');

    });

    Route::group(['prefix' => 'inscriptions'], function () {

        Route::get('/', [InscriptionsController::class, 'index'])->name('distributor.inscriptions');
        Route::post('/store', [InscriptionsController::class, 'store'])->name('distributor.inscriptions.store');
        Route::post('/enroll', [InscriptionsController::class, 'enroll'])->name('distributor.inscriptions.enroll');

        Route::post('/get/users', [InscriptionsController::class, 'getUsers'])->name('distributor.inscriptions.get.users');
        Route::post('/get/courses', [InscriptionsController::class, 'getCourses'])->name('distributor.inscriptions.get.courses');

        Route::get('/massive', [InscriptionsMassivesController::class, 'index'])->name('distributor.inscriptions.massives');
        Route::post('/massive/store', [InscriptionsMassivesController::class, 'store'])->name('distributor.inscriptions.massives.store');
        Route::post('/massive/enroll', [InscriptionsMassivesController::class, 'enroll'])->name('distributor.inscriptions.massives.enroll');

        Route::post('/massive/get/users', [InscriptionsMassivesController::class, 'getUsers'])->name('distributor.inscriptions.massives.get.users');
        Route::post('/massive/get/courses', [InscriptionsMassivesController::class, 'getCourses'])->name('distributor.inscriptions.massives.get.courses');

    });

    Route::group(['prefix' => 'registers'], function () {

        Route::get('/', [RegistersController::class, 'index'])->name('distributor.registers');
        Route::post('/registers/store', [RegistersController::class, 'store'])->name('distributor.registers.store');
        Route::post('/registers/check/identification', [RegistersController::class, 'check'])->name('distributor.registers.users.check');

    });

    Route::group(['prefix' => 'enterprises'], function () {

        Route::get('/', [EnterprisesController::class, 'index'])->name('distributor.enterprises');
        Route::get('/create', [EnterprisesController::class, 'create'])->name('distributor.enterprises.create');
        Route::post('/store', [EnterprisesController::class, 'store'])->name('distributor.enterprises.store');
        Route::post('/update', [EnterprisesController::class, 'update'])->name('distributor.enterprises.update');
        Route::get('/edit/{slack}', [EnterprisesController::class, 'edit'])->name('distributor.enterprises.edit');
        Route::get('/reassign/{slack}', [EnterpriseReassignController::class, 'all'])->name('distributor.enterprises.reassign');
        Route::get('/view/{slack}', [EnterprisesController::class, 'view'])->name('distributor.enterprises.view');
        Route::delete('/destroy/{slack}', [EnterprisesController::class, 'destroy'])->name('distributor.enterprises.destroy');
        Route::get('/navegation/{slack}', [EnterprisesController::class, 'navegation'])->name('distributor.enterprises.navegation');
        Route::get('/inscriptions/{slack}', [EnterpriseInscriptionsController::class, 'index'])->name('distributor.enterprises.inscriptions');
        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'index'])->name('distributor.enterprises.courses');
        Route::get('/users/{slack}', [EnterpriseUserController::class, 'index'])->name('distributor.enterprises.users');
        Route::get('/staff/{slack}', [EnterpriseStaffController::class, 'index'])->name('distributor.enterprises.staffs');
        Route::post('/staff/store', [EnterpriseStaffController::class, 'store'])->name('distributor.enterprises.staffs.store');
        Route::post('/staff/update', [EnterpriseStaffController::class, 'update'])->name('distributor.enterprises.staffs.update');
        Route::post('/users/update', [EnterpriseUserController::class, 'update'])->name('distributor.enterprises.users.update');
        Route::post('/users/store', [EnterpriseUserController::class, 'store'])->name('distributor.enterprises.users.store');
        Route::post('/users/check/identification', [EnterpriseUserController::class, 'check'])->name('distributor.enterprises.users.check');
        Route::post('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('distributor.enterprises.courses.generate');
        Route::post('/users/inscriptions/store', [EnterpriseInscriptionsController::class, 'store'])->name('distributor.enterprises.inscriptions.store');
        Route::post('/users/inscriptions/enroll', [EnterpriseInscriptionsController::class, 'enroll'])->name('distributor.enterprises.inscriptions.enroll');

        Route::post('/users/reassign/single', [EnterpriseReassignController::class, 'reassignSingle'])->name('distributor.enterprises.users.reassign.single');
        Route::post('/users/reassign/all', [EnterpriseReassignController::class, 'reassignAll'])->name('distributor.enterprises.users.reassign.all');
        Route::get('/users/income/generate', [EnterpriseUserController::class, 'incoming'])->name('distributor.enterprises.users.incoming');
        Route::get('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('distributor.enterprises.users.generate');
        Route::get('/courses/reports/generate', [EnterpriseCourseController::class, 'generate'])->name('distributor.enterprises.courses.generate_2');
        Route::get('/users/create/{slack}', [EnterpriseUserController::class, 'create'])->name('distributor.enterprises.users.create');
        Route::get('/users/edit/{slack}', [EnterpriseUserController::class, 'edit'])->name('distributor.enterprises.users.edit');
        Route::get('/users/view/{slack}', [EnterpriseUserController::class, 'view'])->name('distributor.enterprises.users.view');
        Route::get('/users/reports/{slack}', [EnterpriseUserController::class, 'report'])->name('distributor.enterprises.users.reports');
        Route::get('/users/income/{slack}', [EnterpriseUserController::class, 'income'])->name('distributor.enterprises.users.income');
        Route::get('/users/results/{slack}', [ResultsController::class, 'index'])->name('distributor.enterprises.users.results');

        Route::get('/users/reassign/{slack}', [EnterpriseReassignController::class, 'single'])->name('distributor.enterprises.users.reassign');
        Route::get('/courses/assign/{slack}', [EnterpriseCourseController::class, 'assign'])->name('distributor.enterprises.courses.assign');

        Route::post('/courses/assign/update', [EnterpriseCourseController::class, 'update'])->name('distributor.enterprises.courses.assign.update');

        Route::get('/staff/create/{slack}', [EnterpriseStaffController::class, 'create'])->name('distributor.enterprises.staffs.create');
        Route::get('/staff/edit/{slack}', [EnterpriseStaffController::class, 'edit'])->name('distributor.enterprises.staffs.edit');
        Route::delete('/staff/destroy/{slack}', [EnterpriseStaffController::class, 'destroy'])->name('distributor.enterprises.staffs.destroy');
        Route::get('/users/courses/{slack}', [EnterpriseUserController::class, 'courses'])->name('distributor.enterprises.users.courses');
        Route::get('/users/courses/progress/{user}', [EnterpriseCourseController::class, 'progress'])->name('distributor.enterprises.courses.progress');
        Route::get('/users/courses/details/{slack}', [EnterpriseCourseController::class, 'details'])->name('distributor.enterprises.courses.details');
        Route::get('/users/certifications/{slack}', [CertificatesController::class, 'index'])->name('distributor.enterprises.users.certificates');
        Route::get('/users/courses/certificate/{slack}', [CertificatesController::class, 'view'])->name('distributor.certificate.view');
        Route::get('/users/results/view/{slack}', [ResultsController::class, 'view'])->name('distributor.enterprises.users.results.view');
        Route::get('/users/results/download/{slack}', [ResultsController::class, 'download'])->name('distributor.enterprises.users.results.download');
        Route::delete('/users/courses/destroy/{user}', [EnterpriseCourseController::class, 'destroyInscription'])->name('distributor.enterprises.courses.user.destroy');
        Route::get('/users/certificate/user/{slack}', [CertificatesController::class, 'user'])->name('distributor.enterprises.users.certificate.user');
        Route::get('/users/certificate/broad/{slack}', [CertificatesController::class, 'broad'])->name('distributor.enterprises.users.certificate.broad');
        Route::get('/users/certificate/course/{slack}', [CertificatesController::class, 'course'])->name('distributor.enterprises.users.certificate.course');
        Route::get('/users/courses/certificate/download/{slack}', [CertificatesController::class, 'download'])->name('distributor.certificate.download');

        Route::delete('/users/courses/destroy/{enterprice}/{course}', [EnterpriseCourseController::class, 'destroy'])->name('distributor.enterprises.courses.destroy');
        Route::get('/users/courses/reasign/{enterprises}/{course}', [EnterpriseCourseController::class, 'reasign'])->name('distributor.enterprises.courses.reasign');
        Route::get('/users/courses/reports/{enterprises}/{course}', [EnterpriseCourseController::class, 'report'])->name('distributor.enterprises.courses.reports');
        Route::get('/users/courses/view/{enterprises}/{course}', [EnterpriseCourseController::class, 'view'])->name('distributor.enterprises.courses.view');

    });

    Route::group(['prefix' => 'orders'], function () {

        Route::get('/', [OrdersController::class, 'index'])->name('distributor.orders');
        Route::get('/reports', [OrdersReportController::class, 'report'])->name('distributor.orders.reports');
        Route::get('/resumen', [OrdersResumenController::class, 'resumen'])->name('distributor.orders.resumen');
        Route::get('/reports/generate', [OrdersReportController::class, 'generate'])->name('distributor.orders.generate');
        Route::get('/resumen/generate', [OrdersResumenController::class, 'generate'])->name('distributor.orders.resumen.generate');
        Route::get('/print/{slack}', [OrdersController::class, 'print'])->name('distributor.orders.print');
        Route::get('/view/{slack}', [OrdersController::class, 'view'])->name('distributor.orders.view');

    });

    Route::group(['prefix' => 'invoices'], function () {

        Route::get('/', [InvoicesController::class, 'index'])->name('distributor.invoices');
        Route::get('/reports', [InvoicesController::class, 'report'])->name('distributor.invoices.report');
        Route::get('/view/{slack}', [InvoicesController::class, 'view'])->name('distributor.invoices.view');
        Route::get('/detail/{slack}', [InvoicesController::class, 'detail'])->name('distributor.invoices.detail');
        Route::get('/report/generate', [InvoicesController::class, 'generate'])->name('distributor.invoices.generate');

    });

});
