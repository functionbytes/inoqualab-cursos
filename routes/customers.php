<?php

use App\Http\Controllers\Customers\CertificateController;
use App\Http\Controllers\Customers\CoursesController;
use App\Http\Controllers\Customers\DashboardController;
use App\Http\Controllers\Customers\DocumentsController;
use App\Http\Controllers\Customers\ExamController;
use App\Http\Controllers\Customers\InstructionsController;
use App\Http\Controllers\Customers\NotificationsController;
use App\Http\Controllers\Customers\OrdersController;
use App\Http\Controllers\Customers\QuizController;
use App\Http\Controllers\Customers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer', 'middleware' => ['auth', 'verified', 'customers', 'upgrade', 'session']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('customers.dashboard');
    Route::post('/ping', fn () => response()->json(['ok' => true]))->name('customers.ping');

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'index'])->name('customers.orders')->middleware('profile');
        Route::get('/view/{slack}', [OrdersController::class, 'view'])->name('customers.orders.view');
        Route::get('/payment/{slack}', [OrdersController::class, 'payment'])->name('customers.orders.payments');
    });

    Route::group(['prefix' => 'instructions'], function () {
        Route::get('/', [InstructionsController::class, 'index'])->name('customers.instructions');
        Route::get('/view/{slack}', [InstructionsController::class, 'view'])->name('customers.instructions.view');
        Route::get('/filter', [InstructionsController::class, 'filter'])->name('customers.instructions.filter');
    });

    Route::group(['prefix' => 'courses'], function () {

        Route::get('/', [CoursesController::class, 'index'])->name('customers.courses')->middleware('profile');
        Route::get('/content/{slug}', [CoursesController::class, 'content'])->name('customers.courses.content');
        Route::get('/content/lesion/{slug}', [CoursesController::class, 'lesion'])->name('customers.courses.lesion');
        Route::get('/content/player/{lesson}', [CoursesController::class, 'player'])->name('customers.courses.player');
        Route::post('/content/lesion/realized', [CoursesController::class, 'realized'])->name('customers.courses.realized')->middleware('throttle:30,1');
        Route::post('/content/lesion/prev', [CoursesController::class, 'prev'])->name('customers.courses.prev');
        Route::post('/content/review', [CoursesController::class, 'review'])->name('customers.courses.review')->middleware('throttle:10,1');

        Route::get('/certificates', [CertificateController::class, 'index'])->name('customers.certificates');
        Route::get('/certificate/view/{slack}', [CertificateController::class, 'view'])->name('customers.certificate.view');
        Route::get('/certificate/download/{slack}', [CertificateController::class, 'download'])->name('customers.certificate.download')->middleware('throttle:30,1');

        Route::post('/content/quiz/realized', [QuizController::class, 'realized'])->name('customers.quiz.realized')->middleware('throttle:20,1');
        Route::get('/content/quiz/{slug}', [QuizController::class, 'quiz'])->name('customers.courses.quiz');
        Route::get('/content/quiz/finish/{id}', [QuizController::class, 'finish'])->name('customers.quiz.show');
        Route::get('/content/quiz/tryagain/{slug}', [QuizController::class, 'tryagain'])->name('customers.quiz.tryagain');
        Route::post('/content/quiz/store/{id}', [QuizController::class, 'store'])->name('customers.quiz.store')->middleware('throttle:20,1');

        Route::get('/content/exam/{slug}', [ExamController::class, 'exam'])->name('customers.courses.exam');
        Route::get('/content/exam/finish/{id}', [ExamController::class, 'finish'])->name('customers.exam.show');
        Route::get('/content/exam/tryagain/{slug}', [ExamController::class, 'tryagain'])->name('customers.exam.tryagain');
        Route::post('/content/exam/store/{id}', [ExamController::class, 'store'])->name('customers.exam.store')->middleware('throttle:20,1');
    });

    Route::group(['prefix' => 'documents'], function () {
        Route::get('/', [DocumentsController::class, 'index'])->name('customers.documents')->middleware('profile');
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('customers.settings');
        Route::post('/update', [SettingsController::class, 'update'])->name('customers.settings.update');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationsController::class, 'index'])->name('customers.notifications');
        Route::get('/view/{id}', [NotificationsController::class, 'view'])->name('customers.notiication.view');
        Route::post('/mark', [NotificationsController::class, 'mark'])->name('customers.notifications.mark');
        Route::post('/search', [NotificationsController::class, 'search'])->name('customers.notifications.search');
        Route::delete('/delete', [NotificationsController::class, 'delete'])->name('customers.notifications.delete');
    });

});
