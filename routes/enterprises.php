<?php

use App\Http\Controllers\Enterprises\Dashboard\DashboardController;
use App\Http\Controllers\Enterprises\Enterprises\CoursesController;
use App\Http\Controllers\Enterprises\Enterprises\EnterprisesController;
use App\Http\Controllers\Enterprises\NotificationsController;
use App\Http\Controllers\Enterprises\Settings\DocumentsController;
use App\Http\Controllers\Enterprises\Settings\SettingsController;
use App\Http\Controllers\Enterprises\Users\CertificatesController;
use App\Http\Controllers\Enterprises\Users\ReportController;
use App\Http\Controllers\Enterprises\Users\ResultsController;
use App\Http\Controllers\Enterprises\Users\UsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'enterprise', 'middleware' => ['auth', 'enterprise', 'session', 'panel.permission']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('enterprise.dashboard');

    Route::group(['prefix' => 'notifications'], function () {

        Route::get('/', [NotificationsController::class, 'index'])->name('enterprise.notifications');
        Route::get('/mark-as-read', [NotificationsController::class, 'markasread'])->name('enterprise.notifications.markasread');

    });

    Route::group(['prefix' => 'documents'], function () {
        Route::get('/', [DocumentsController::class, 'index'])->name('enterprise.documents');
    });

    Route::group(['prefix' => 'enterprises'], function () {
        Route::get('/', [EnterprisesController::class, 'index'])->name('enterprise.enterprises');
        Route::post('/update', [EnterprisesController::class, 'update'])->name('enterprise.enterprise.update');
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('enterprise.profile');
        Route::post('/update', [SettingsController::class, 'update'])->name('enterprise.profile.update');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index'])->name('enterprise.users');
        Route::get('/reports', [UsersController::class, 'report'])->name('enterprise.users.reports');
        Route::post('/update', [UsersController::class, 'update'])->name('enterprise.users.update');
        Route::get('/edit/{slack}', [UsersController::class, 'edit'])->name('enterprise.users.edit');
        Route::get('/results/{slack}', [ResultsController::class, 'index'])->name('enterprise.users.results');
        Route::post('/reports/generate', [UsersController::class, 'generate'])->name('enterprise.users.generate');
        Route::get('/certification/{slack}', [CertificatesController::class, 'index'])->name('enterprise.users.certificates');
        Route::get('/certifications/{slack}', [CertificatesController::class, 'broad'])->name('enterprise.users.certificate.broad');
        Route::get('/results/view/{slack}', [ResultsController::class, 'view'])->name('enterprise.users.results.view');
        Route::get('/results/download/{slack}', [ResultsController::class, 'download'])->name('enterprise.users.results.download');
        Route::get('/certificate/course/{slack}', [CertificatesController::class, 'course'])->name('enterprise.users.certificate.course');
        Route::get('/certificate/user/{slack}', [CertificatesController::class, 'user'])->name('enterprise.users.certificate.user');
    });

    Route::group(['prefix' => 'courses'], function () {
        Route::get('/', [CoursesController::class, 'index'])->name('enterprise.courses');
        Route::get('/progress/{slack}', [CoursesController::class, 'progress'])->name('enterprise.courses.progress');
        Route::get('/details/{slack}', [CoursesController::class, 'details'])->name('enterprise.courses.details');
        Route::get('/details/view/{slack}', [CoursesController::class, 'view'])->name('enterprise.courses.view');
        Route::get('/reports/{slack}', [ReportController::class, 'report'])->name('enterprise.courses.reports');
        Route::get('/reports/generate', [ReportController::class, 'generate'])->name('enterprise.courses.generate');
    });

});
