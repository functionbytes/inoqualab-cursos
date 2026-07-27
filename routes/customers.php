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

Route::group(['prefix' => 'customer', 'middleware' => ['auth', 'customers', 'session']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('customers.dashboard');
    Route::post('/ping', fn () => response()->json(['ok' => true]))->name('customers.ping');

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'index'])->name('customers.orders');
        Route::get('/view/{slack}', [OrdersController::class, 'view'])->name('customers.orders.view');
        Route::get('/invoice/{slack}', [OrdersController::class, 'invoice'])->name('customers.orders.invoice');
        Route::get('/payment/{slack}', [OrdersController::class, 'payment'])->name('customers.orders.payments');
    });

    Route::group(['prefix' => 'instructions'], function () {
        Route::get('/', [InstructionsController::class, 'index'])->name('customers.instructions');
        Route::get('/view/{slack}', [InstructionsController::class, 'view'])->name('customers.instructions.view');
        Route::get('/filter', [InstructionsController::class, 'filter'])->name('customers.instructions.filter');
    });

    Route::group(['prefix' => 'courses'], function () {

        // Los parámetros llevan el nombre de lo que realmente reciben. Varios
        // se llamaban {slug} sin serlo: /content/ toma el slack de la
        // inscripción y /content/lesion/ el id numérico de la lección, lo que
        // hacía perder tiempo cada vez que alguien construía una URL a mano.
        // Todos los enlaces pasan el valor por posición, así que renombrarlos
        // no afecta a ninguna llamada a route().
        Route::get('/', [CoursesController::class, 'index'])->name('customers.courses');
        Route::get('/content/{inscription}', [CoursesController::class, 'content'])->name('customers.courses.content');
        Route::get('/content/lesion/{lesson}', [CoursesController::class, 'lesion'])->name('customers.courses.lesion');
        Route::get('/content/player/{lesson}', [CoursesController::class, 'player'])->name('customers.courses.player');
        Route::post('/content/lesion/realized', [CoursesController::class, 'realized'])->name('customers.courses.realized')->middleware('throttle:30,1');
        Route::post('/content/lesion/prev', [CoursesController::class, 'prev'])->name('customers.courses.prev');
        Route::post('/content/review', [CoursesController::class, 'review'])->name('customers.courses.review')->middleware('throttle:10,1');

        Route::get('/certificates', [CertificateController::class, 'index'])->name('customers.certificates');
        Route::get('/certificate/view/{slack}', [CertificateController::class, 'view'])->name('customers.certificate.view');
        Route::get('/certificate/download/{slack}', [CertificateController::class, 'download'])->name('customers.certificate.download')->middleware('throttle:30,1');

        Route::post('/content/quiz/realized', [QuizController::class, 'realized'])->name('customers.quiz.realized')->middleware('throttle:20,1');
        Route::get('/content/quiz/{lesson}', [QuizController::class, 'quiz'])->name('customers.courses.quiz');
        Route::get('/content/quiz/finish/{quiz}', [QuizController::class, 'finish'])->name('customers.quiz.show');
        Route::get('/content/quiz/tryagain/{quiz}', [QuizController::class, 'tryagain'])->name('customers.quiz.tryagain');
        // El segmento de URL llega al controller pero no se usa: store() toma el
        // quiz de $request->quiz y valida que sea del usuario autenticado.
        Route::post('/content/quiz/store/{lesson}', [QuizController::class, 'store'])->name('customers.quiz.store')->middleware('throttle:20,1');

        // Este sí recibe el slack del curso, no un id.
        Route::get('/content/exam/{course}', [ExamController::class, 'exam'])->name('customers.courses.exam');
        Route::get('/content/exam/finish/{exam}', [ExamController::class, 'finish'])->name('customers.exam.show');
        Route::get('/content/exam/tryagain/{exam}', [ExamController::class, 'tryagain'])->name('customers.exam.tryagain');
        // Recibe el id del ExamTopic, no el del curso ni el del examen.
        Route::post('/content/exam/store/{topic}', [ExamController::class, 'store'])->name('customers.exam.store')->middleware('throttle:20,1');
    });

    Route::group(['prefix' => 'documents'], function () {
        Route::get('/', [DocumentsController::class, 'index'])->name('customers.documents');
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('customers.settings');
        Route::post('/update', [SettingsController::class, 'update'])->name('customers.settings.update');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationsController::class, 'index'])->name('customers.notifications');
        Route::get('/view/{id}', [NotificationsController::class, 'view'])->name('customers.notifications.view');
        Route::post('/mark', [NotificationsController::class, 'mark'])->name('customers.notifications.mark');
        Route::post('/search', [NotificationsController::class, 'search'])->name('customers.notifications.search');
        Route::delete('/delete', [NotificationsController::class, 'delete'])->name('customers.notifications.delete');
    });

});
