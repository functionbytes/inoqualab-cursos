<?php

use App\Http\Controllers\Supports\Contacts\ContactsController;
use App\Http\Controllers\Supports\DashboardController;
use App\Http\Controllers\Supports\Distributors\CourseController as DistributorCourseController;
use App\Http\Controllers\Supports\Distributors\DistributorsController;
use App\Http\Controllers\Supports\Distributors\EnterpriseController as DistributorEnterpriseController;
use App\Http\Controllers\Supports\Distributors\InscriptionsController as DistributorsInscriptionsController;
use App\Http\Controllers\Supports\Distributors\InscriptionsMassivesController as DistributorsInscriptionsMassivesController;
use App\Http\Controllers\Supports\Distributors\InvoicesController as DistributorsInvoicesController;
use App\Http\Controllers\Supports\Distributors\Orders\OrdersController as DistributorsOrdersController;
use App\Http\Controllers\Supports\Distributors\Orders\ReportController as OrdersReportController;
use App\Http\Controllers\Supports\Distributors\Orders\ResumenController as OrdersResumenController;
use App\Http\Controllers\Supports\Distributors\RatesController as DistributorRatesController;
use App\Http\Controllers\Supports\Distributors\RegistersController;
use App\Http\Controllers\Supports\Distributors\StaffController as DistributorStaffController;
use App\Http\Controllers\Supports\Documents\DocumentsController;
use App\Http\Controllers\Supports\Enterprises\CourseController as EnterpriseCourseController;
use App\Http\Controllers\Supports\Enterprises\EnterprisesController;
use App\Http\Controllers\Supports\Enterprises\InscriptionsController as EnterpriseInscriptionsController;
use App\Http\Controllers\Supports\Enterprises\ReassignController as EnterpriseReassignController;
use App\Http\Controllers\Supports\Enterprises\StaffController as EnterpriseStaffController;
use App\Http\Controllers\Supports\Enterprises\UserController as EnterpriseUserController;
use App\Http\Controllers\Supports\Faqs\CategoriesController as FaqsCategoriesController;
use App\Http\Controllers\Supports\Faqs\FaqsController;
use App\Http\Controllers\Supports\IncomingMails\IncomingMailsController;
use App\Http\Controllers\Supports\Instructions\CategoriesController as InstructionsCategoriesController;
use App\Http\Controllers\Supports\Instructions\InstructionsController;
use App\Http\Controllers\Supports\NotificationsController;
use App\Http\Controllers\Supports\Settings\SettingsController;
use App\Http\Controllers\Supports\Users\ActivitysController as UsersActivitysController;
use App\Http\Controllers\Supports\Users\CertificatesController;
use App\Http\Controllers\Supports\Users\CertificatesController as UsersCertificatesController;
use App\Http\Controllers\Supports\Users\InscriptionsController as UsersInscriptionsController;
use App\Http\Controllers\Supports\Users\ManagementController as EnterpriseUserManagementController;
use App\Http\Controllers\Supports\Users\OrdersController as UsersOrdersController;
use App\Http\Controllers\Supports\Users\ResultsController;
use App\Http\Controllers\Supports\Users\UsersController;
use App\Http\Controllers\Supports\Users\UsersCoursesController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'support', 'middleware' => ['auth', 'support', 'session', 'panel.permission']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('support.dashboard');

    Route::group(['prefix' => 'notifications'], function () {

        Route::get('/', [NotificationsController::class, 'index'])->name('support.notifications');
        Route::get('/mark-as-read', [NotificationsController::class, 'markasread'])->name('support.notifications.markasread');
        Route::get('/all', [NotificationsController::class, 'show'])->name('support.notifications.all');

    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/profile', [SettingsController::class, 'profile'])->name('support.settings.profile');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('support.settings.notifications');
        Route::post('/profile/update', [SettingsController::class, 'updateProfile'])->name('support.settings.profile.update');
        Route::post('/notifications/update', [SettingsController::class, 'updateNotifications'])->name('support.settings.notifications.update');
    });

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('support.documents');
        Route::get('/create', [DocumentsController::class, 'create'])->name('support.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('support.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('support.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('support.documents.edit');
        Route::delete('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('support.documents.destroy');
        Route::post('/bulk-action', [DocumentsController::class, 'bulkAction'])->name('support.documents.bulk-action');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('support.documents.files');
        Route::delete('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('support.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('support.documents.files.get');

    });

    Route::group(['prefix' => 'contacts'], function () {

        Route::get('/', [ContactsController::class, 'index'])->name('support.contacts');
        Route::post('/update', [ContactsController::class, 'update'])->name('support.contacts.update');
        Route::get('/edit/{slack}', [ContactsController::class, 'edit'])->name('support.contacts.edit');
        Route::get('/view/{slack}', [ContactsController::class, 'view'])->name('support.contacts.view');
        Route::delete('/destroy/{slack}', [ContactsController::class, 'destroy'])->name('support.contacts.destroy');
        Route::post('/bulk-action', [ContactsController::class, 'bulkAction'])->name('support.contacts.bulk-action');

    });

    Route::group(['prefix' => 'instructions'], function () {

        Route::get('/', [InstructionsController::class, 'index'])->name('support.instructions');
        Route::get('/create', [InstructionsController::class, 'create'])->name('support.instructions.create');
        Route::post('/store', [InstructionsController::class, 'store'])->name('support.instructions.store');
        Route::post('/update', [InstructionsController::class, 'update'])->name('support.instructions.update');
        Route::get('/edit/{slack}', [InstructionsController::class, 'edit'])->name('support.instructions.edit');
        Route::delete('/destroy/{slack}', [InstructionsController::class, 'destroy'])->name('support.instructions.destroy');
        Route::post('/bulk-action', [InstructionsController::class, 'bulkAction'])->name('support.instructions.bulk-action');

        Route::get('/categories', [InstructionsCategoriesController::class, 'index'])->name('support.instructions.categories');
        Route::get('/categories/create', [InstructionsCategoriesController::class, 'create'])->name('support.instructions.categories.create');
        Route::post('/categories/store', [InstructionsCategoriesController::class, 'store'])->name('support.instructions.categories.store');
        Route::post('/categories/update', [InstructionsCategoriesController::class, 'update'])->name('support.instructions.categories.update');
        Route::get('/categories/edit/{slack}', [InstructionsCategoriesController::class, 'edit'])->name('support.instructions.categories.edit');
        Route::delete('/categories/destroy/{slack}', [InstructionsCategoriesController::class, 'destroy'])->name('support.instructions.categories.destroy');
        Route::post('/categories/bulk-action', [InstructionsCategoriesController::class, 'bulkAction'])->name('support.instructions.categories.bulk-action');

    });

    Route::group(['prefix' => 'mails'], function () {

        Route::get('/get-enterprises', [IncomingMailsController::class, 'getEnterprises'])->name('support.mails.enterprises');
        Route::get('/get-courses', [IncomingMailsController::class, 'getCourses'])->name('support.mails.courses');
        Route::get('/', [IncomingMailsController::class, 'index'])->name('support.mails.index');
        Route::get('/{slack}', [IncomingMailsController::class, 'show'])->name('support.mails.show');
        Route::post('/{slack}/confirm', [IncomingMailsController::class, 'confirm'])->name('support.mails.confirm');
        Route::post('/{slack}/discard', [IncomingMailsController::class, 'discard'])->name('support.mails.discard');
        Route::post('/{slack}/reparse', [IncomingMailsController::class, 'reparse'])->name('support.mails.reparse');
        Route::post('/bulk-discard', [IncomingMailsController::class, 'bulkDiscard'])->name('support.mails.bulk-discard');

    });

    Route::group(['prefix' => 'faqs'], function () {

        Route::get('/', [FaqsController::class, 'index'])->name('support.faqs');
        Route::get('/create', [FaqsController::class, 'create'])->name('support.faqs.create');
        Route::post('/store', [FaqsController::class, 'store'])->name('support.faqs.store');
        Route::post('/update', [FaqsController::class, 'update'])->name('support.faqs.update');
        Route::get('/edit/{slack}', [FaqsController::class, 'edit'])->name('support.faqs.edit');
        Route::delete('/destroy/{slack}', [FaqsController::class, 'destroy'])->name('support.faqs.destroy');
        Route::post('/bulk-action', [FaqsController::class, 'bulkAction'])->name('support.faqs.bulk-action');

        Route::get('/categories', [FaqsCategoriesController::class, 'index'])->name('support.faqs.categories');
        Route::get('/categories/create', [FaqsCategoriesController::class, 'create'])->name('support.faqs.categories.create');
        Route::post('/categories/store', [FaqsCategoriesController::class, 'store'])->name('support.faqs.categories.store');
        Route::post('/categories/update', [FaqsCategoriesController::class, 'update'])->name('support.faqs.categories.update');
        Route::get('/categories/edit/{slack}', [FaqsCategoriesController::class, 'edit'])->name('support.faqs.categories.edit');
        Route::delete('/categories/destroy/{slack}', [FaqsCategoriesController::class, 'destroy'])->name('support.faqs.categories.destroy');
        Route::post('/categories/bulk-action', [FaqsCategoriesController::class, 'bulkAction'])->name('support.faqs.categories.bulk-action');

    });

    Route::group(['prefix' => 'enterprises'], function () {

        Route::get('/', [EnterprisesController::class, 'index'])->name('support.enterprises');
        Route::get('/create', [EnterprisesController::class, 'create'])->name('support.enterprises.create');
        Route::post('/store', [EnterprisesController::class, 'store'])->name('support.enterprises.store');
        Route::post('/update', [EnterprisesController::class, 'update'])->name('support.enterprises.update');
        Route::get('/edit/{slack}', [EnterprisesController::class, 'edit'])->name('support.enterprises.edit');
        Route::get('/reassign/{slack}', [EnterpriseReassignController::class, 'all'])->name('support.enterprises.reassign');
        Route::delete('/destroy/{slack}', [EnterprisesController::class, 'destroy'])->name('support.enterprises.destroy');
        Route::post('/bulk-action', [EnterprisesController::class, 'bulkAction'])->name('support.enterprises.bulk-action');
        Route::get('/navegation/{slack}', [EnterprisesController::class, 'navegation'])->name('support.enterprises.navegation');
        Route::get('/inscriptions/{slack}', [EnterpriseInscriptionsController::class, 'index'])->name('support.enterprises.inscriptions');
        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'index'])->name('support.enterprises.courses');
        Route::get('/users/{slack}', [EnterpriseUserController::class, 'index'])->name('support.enterprises.users');
        Route::get('/staff/{slack}', [EnterpriseStaffController::class, 'index'])->name('support.enterprises.staffs');
        Route::post('/staff/store', [EnterpriseStaffController::class, 'store'])->name('support.enterprises.staffs.store');
        Route::post('/staff/update', [EnterpriseStaffController::class, 'update'])->name('support.enterprises.staffs.update');
        Route::post('/users/update', [EnterpriseUserController::class, 'update'])->name('support.enterprises.users.update');
        Route::post('/users/store', [EnterpriseUserController::class, 'store'])->name('support.enterprises.users.store');
        Route::post('/users/check/identification', [EnterpriseUserController::class, 'check'])->name('support.enterprises.users.check');
        Route::post('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('support.enterprises.users.reports.generate');
        Route::post('/users/inscriptions/store', [EnterpriseInscriptionsController::class, 'store'])->name('support.enterprises.inscriptions.store');
        Route::post('/users/inscriptions/enroll', [EnterpriseInscriptionsController::class, 'enroll'])->name('support.enterprises.inscriptions.enroll')->middleware('throttle:10,1');

        Route::post('/users/reassign/single', [EnterpriseReassignController::class, 'reassignSingle'])->name('support.enterprises.users.reassign.single');
        Route::post('/users/reassign/all', [EnterpriseReassignController::class, 'reassignAll'])->name('support.enterprises.users.reassign.all');
        Route::get('/users/income/generate', [EnterpriseUserController::class, 'incoming'])->name('support.enterprises.users.incoming');
        Route::get('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('support.enterprises.users.generate');
        Route::get('/courses/reports/generate', [EnterpriseCourseController::class, 'generate'])->name('support.enterprises.courses.generate');
        Route::get('/users/create/{slack}', [EnterpriseUserController::class, 'create'])->name('support.enterprises.users.create');
        Route::get('/users/edit/{slack}', [EnterpriseUserController::class, 'edit'])->name('support.enterprises.users.edit');
        Route::get('/users/view/{slack}', [EnterpriseUserController::class, 'view'])->name('support.enterprises.users.view');
        Route::get('/users/reports/{slack}', [EnterpriseUserController::class, 'report'])->name('support.enterprises.users.reports');
        Route::get('/users/income/{slack}', [EnterpriseUserController::class, 'income'])->name('support.enterprises.users.income');
        Route::get('/users/results/{slack}', [ResultsController::class, 'index'])->name('support.enterprises.users.results');

        Route::get('/users/reassign/{slack}', [EnterpriseReassignController::class, 'single'])->name('support.enterprises.users.reassign');
        Route::get('/courses/assign/{slack}', [EnterpriseCourseController::class, 'assign'])->name('support.enterprises.courses.assign');

        Route::post('/courses/assign/update', [EnterpriseCourseController::class, 'update'])->name('support.enterprises.courses.assign.update');

        Route::get('/staff/create/{slack}', [EnterpriseStaffController::class, 'create'])->name('support.enterprises.staffs.create');
        Route::get('/staff/edit/{slack}', [EnterpriseStaffController::class, 'edit'])->name('support.enterprises.staffs.edit');
        Route::delete('/staff/destroy/{slack}', [EnterpriseStaffController::class, 'destroy'])->name('support.enterprises.staffs.destroy');
        Route::post('/staff/bulk-action', [EnterpriseStaffController::class, 'bulkAction'])->name('support.enterprises.staffs.bulk-action');
        Route::get('/users/courses/{slack}', [EnterpriseUserController::class, 'courses'])->name('support.enterprises.users.courses');
        Route::get('/users/courses/progress/{user}', [EnterpriseCourseController::class, 'progress'])->name('support.enterprises.courses.progress');
        Route::get('/users/courses/details/{slack}', [EnterpriseCourseController::class, 'details'])->name('support.enterprises.courses.details');
        Route::get('/users/certifications/{slack}', [CertificatesController::class, 'index'])->name('support.enterprises.users.certificates');
        Route::get('/users/results/view/{slack}', [ResultsController::class, 'view'])->name('support.enterprises.users.results.view');
        Route::get('/users/results/download/{slack}', [ResultsController::class, 'download'])->name('support.enterprises.users.results.download');
        Route::delete('/users/courses/destroy/{user}', [EnterpriseCourseController::class, 'destroyInscription'])->name('support.enterprises.courses.user.destroy');
        Route::get('/users/certificate/user/{slack}', [CertificatesController::class, 'user'])->name('support.enterprises.users.certificate.user');
        Route::get('/users/certificate/broad/{slack}', [CertificatesController::class, 'broad'])->name('support.enterprises.users.certificate.broad');
        Route::get('/users/certificate/course/{slack}', [CertificatesController::class, 'course'])->name('support.enterprises.users.certificate.course');

        Route::delete('/users/courses/destroy/{enterprice}/{course}', [EnterpriseCourseController::class, 'destroy'])->name('support.enterprises.courses.destroy');
        Route::get('/users/courses/reasign/{enterprises}/{course}', [EnterpriseCourseController::class, 'reasign'])->name('support.enterprises.courses.reasign');
        Route::post('/users/courses/reasign/action', [EnterpriseCourseController::class, 'includes'])->name('support.enterprises.action.reasign');
        Route::get('/users/courses/reports/{enterprises}/{course}', [EnterpriseCourseController::class, 'report'])->name('support.enterprises.courses.reports');
        Route::get('/users/courses/view/{enterprises}/{course}', [EnterpriseCourseController::class, 'view'])->name('support.enterprises.courses.view');

        Route::get('/users/courses/managements/{slack}', [EnterpriseUserManagementController::class, 'index'])->name('support.enterprises.users.managements');

        Route::get('/users/courses/managements/inscription/progress/view/{inscription}', [EnterpriseUserManagementController::class, 'progressView'])->name('support.enterprises.users.managements.progress.view');
        // DELETE, no GET: son operaciones destructivas (borran progreso real).
        // El JS de .confirm-delete (public/supports/js/includes/scripts.js) ya
        // envia POST+_method=DELETE con CSRF -- con la ruta como GET, el click
        // normal del boton daba 405 (Method Not Allowed) SIEMPRE, y ademas la
        // ruta GET seguia siendo alcanzable directo (vulnerable a CSRF por
        // <img>/prefetch, sin token).
        Route::delete('/users/courses/managements/inscription/progress/restore/{inscription}', [EnterpriseUserManagementController::class, 'progressRestore'])->name('support.enterprises.users.managements.progress.restore');
        Route::delete('/users/courses/managements/inscription/progress/single/{inscription}', [EnterpriseUserManagementController::class, 'progressRestoreSingle'])->name('support.enterprises.users.managements.progress.restore.single');

        Route::get('/users/courses/managements/inscription/quiz/view/{inscription}', [EnterpriseUserManagementController::class, 'quizView'])->name('support.enterprises.users.managements.quiz.view');
        Route::delete('/users/courses/managements/inscription/quiz/restore/{inscription}', [EnterpriseUserManagementController::class, 'quizRestore'])->name('support.enterprises.users.managements.quiz.restore');

        Route::get('/users/courses/managements/inscription/exam/view/{inscription}', [EnterpriseUserManagementController::class, 'examView'])->name('support.enterprises.users.managements.exam.view');
        Route::delete('/users/courses/managements/inscription/exam/restore/{inscription}', [EnterpriseUserManagementController::class, 'examRestore'])->name('support.enterprises.users.managements.exam.restore');

    });

    Route::group(['prefix' => 'distributors'], function () {

        Route::get('/', [DistributorsController::class, 'index'])->name('support.distributors');
        Route::get('/create', [DistributorsController::class, 'create'])->name('support.distributors.create');
        Route::post('/store', [DistributorsController::class, 'store'])->name('support.distributors.store');
        Route::post('/update', [DistributorsController::class, 'update'])->name('support.distributors.update');
        Route::get('/edit/{slack}', [DistributorsController::class, 'edit'])->name('support.distributors.edit');
        Route::get('/view/{slack}', [DistributorsController::class, 'view'])->name('support.distributors.view');
        Route::delete('/destroy/{slack}', [DistributorsController::class, 'destroy'])->name('support.distributors.destroy');
        Route::post('/bulk-action', [DistributorsController::class, 'bulkAction'])->name('support.distributors.bulk-action');
        Route::get('/navegation/{slack}', [DistributorsController::class, 'navegation'])->name('support.distributors.navegation');

        Route::get('/courses/{slack}', [DistributorCourseController::class, 'index'])->name('support.distributors.courses');
        Route::post('/courses/update', [DistributorCourseController::class, 'update'])->name('support.distributors.courses.update');

        Route::get('/invoices/{slack}', [DistributorsInvoicesController::class, 'index'])->name('support.distributors.invoices');
        Route::get('/invoices/reports/alls', [DistributorsInvoicesController::class, 'report'])->name('support.distributors.invoices.report');
        Route::get('/invoices/view/{slack}', [DistributorsInvoicesController::class, 'view'])->name('support.distributors.invoices.view');
        Route::get('/invoices/detail/{slack}', [DistributorsInvoicesController::class, 'detail'])->name('support.distributors.invoices.detail');
        Route::get('/invoices/report/generate', [DistributorsInvoicesController::class, 'generate'])->name('support.distributors.invoices.generate');

        Route::get('/enterprises/{slack}', [DistributorEnterpriseController::class, 'index'])->name('support.distributors.enterprises');
        Route::get('/enterprises/create/{slack}', [DistributorEnterpriseController::class, 'create'])->name('support.distributors.enterprises.create');
        Route::get('/enterprises/edit/{slack}', [DistributorEnterpriseController::class, 'edit'])->name('support.distributors.enterprises.edit');
        Route::post('/enterprises/update', [DistributorEnterpriseController::class, 'update'])->name('support.distributors.enterprises.update');
        Route::post('/enterprises/store', [DistributorEnterpriseController::class, 'store'])->name('support.distributors.enterprises.store');
        Route::delete('/enterprises/destroy/{slack}', [DistributorEnterpriseController::class, 'destroy'])->name('support.distributors.enterprises.destroy');
        Route::post('/enterprises/bulk-action', [DistributorEnterpriseController::class, 'bulkAction'])->name('support.distributors.enterprises.bulk-action');

        Route::get('/orders/reports/generate', [OrdersReportController::class, 'generate'])->name('support.distributors.orders.generate');
        Route::get('/orders/resumen/generate', [OrdersResumenController::class, 'generate'])->name('support.distributors.orders.resumen.generate');

        Route::get('/orders/resumen/{slack}', [OrdersResumenController::class, 'resumen'])->name('support.distributors.orders.resumen');
        Route::get('/orders/reports/{slack}', [OrdersReportController::class, 'report'])->name('support.distributors.orders.reports');
        Route::get('/enterprises/assignments/{slack}', [DistributorEnterpriseController::class, 'assignments'])->name('support.distributors.enterprises.assignments');
        Route::post('/enterprises/assignments/update', [DistributorEnterpriseController::class, 'updateAssignments'])->name('support.distributors.enterprises.assignments.update');

        Route::get('/rates/{slack}', [DistributorRatesController::class, 'index'])->name('support.distributors.rates');
        Route::post('/rates/update', [DistributorRatesController::class, 'update'])->name('support.distributors.rates.update');

        Route::get('/staff/{slack}', [DistributorStaffController::class, 'index'])->name('support.distributors.staffs');
        Route::post('/staff/store', [DistributorStaffController::class, 'store'])->name('support.distributors.staffs.store');
        Route::post('/staff/update', [DistributorStaffController::class, 'update'])->name('support.distributors.staffs.update');
        Route::get('/staff/create/{slack}', [DistributorStaffController::class, 'create'])->name('support.distributors.staffs.create');
        Route::get('/staff/edit/{slack}', [DistributorStaffController::class, 'edit'])->name('support.distributors.staffs.edit');
        Route::get('/staff/view/{slack}', [DistributorStaffController::class, 'view'])->name('support.distributors.staffs.view');
        Route::get('/staff/reports/generate', [DistributorStaffController::class, 'generate'])->name('support.distributors.staffs.reports.generate');
        Route::get('/staff/reports/{slack}', [DistributorStaffController::class, 'reports'])->name('support.distributors.staffs.reports');
        Route::delete('/staff/destroy/{slack}', [DistributorStaffController::class, 'destroy'])->name('support.distributors.staffs.destroy');
        Route::post('/staff/bulk-action', [DistributorStaffController::class, 'bulkAction'])->name('support.distributors.staffs.bulk-action');
        Route::get('/staff/history/{slack}', [DistributorStaffController::class, 'history'])->name('support.distributors.staffs.history');

        Route::get('/orders/{slack}', [DistributorsOrdersController::class, 'index'])->name('support.distributors.orders');
        Route::get('/orders/view/{slack}', [DistributorsOrdersController::class, 'view'])->name('support.distributors.orders.view');

        Route::get('/inscriptions/massive/{slack}', [DistributorsInscriptionsMassivesController::class, 'index'])->name('support.distributors.inscriptions.massives');
        Route::post('/inscriptions/massive/store', [DistributorsInscriptionsMassivesController::class, 'store'])->name('support.distributors.inscriptions.massives.store');
        Route::post('/inscriptions/massive/enroll', [DistributorsInscriptionsMassivesController::class, 'enroll'])->name('support.distributors.inscriptions.massives.enroll')->middleware('throttle:10,1');

        Route::post('/inscriptions/massive/get/users', [DistributorsInscriptionsMassivesController::class, 'getUsers'])->name('support.distributors.inscriptions.massives.get.users');
        Route::post('/inscriptions/massive/get/courses', [DistributorsInscriptionsMassivesController::class, 'getCourses'])->name('support.distributors.inscriptions.massives.get.courses');

        Route::get('/inscriptions/{slack}', [DistributorsInscriptionsController::class, 'index'])->name('support.distributors.inscriptions');
        Route::post('/inscriptions/store', [DistributorsInscriptionsController::class, 'store'])->name('support.distributors.inscriptions.store');
        Route::post('/inscriptions/enroll', [DistributorsInscriptionsController::class, 'enroll'])->name('support.distributors.inscriptions.enroll')->middleware('throttle:10,1');

        Route::post('/inscriptions/get/users', [DistributorsInscriptionsController::class, 'getUsers'])->name('support.distributors.inscriptions.get.users');
        Route::post('/inscriptions/get/courses', [DistributorsInscriptionsController::class, 'getCourses'])->name('support.distributors.inscriptions.get.courses');

        Route::get('/registers/{slack}', [RegistersController::class, 'index'])->name('support.distributors.registers');
        Route::post('/registers/store', [RegistersController::class, 'store'])->name('support.distributors.registers.store');
        Route::post('/registers/check/identification', [RegistersController::class, 'check'])->name('support.distributors.registers.users.check');

    });

    Route::group(['prefix' => 'users'], function () {

        Route::get('/', [UsersController::class, 'index'])->name('support.users');
        Route::get('/create', [UsersController::class, 'create'])->name('support.users.create');
        Route::post('/store', [UsersController::class, 'store'])->name('support.users.store');
        Route::post('/update', [UsersController::class, 'update'])->name('support.users.update');
        Route::get('/edit/{slack}', [UsersController::class, 'edit'])->name('support.users.edit');
        Route::get('/view/{slack}', [UsersController::class, 'view'])->name('support.users.view');
        Route::delete('/destroy/{slack}', [UsersController::class, 'destroy'])->name('support.users.destroy');
        Route::post('/bulk-action', [UsersController::class, 'bulkAction'])->name('support.users.bulk-action');
        Route::get('/navegation/{slack}', [UsersController::class, 'navegation'])->name('support.users.navegation');

        Route::post('/information/update', [UsersController::class, 'information'])->name('support.users.information');
        Route::post('/notification/update', [UsersController::class, 'notification'])->name('support.users.notification');
        Route::post('/forgotpassword/update', [UsersController::class, 'forgotpassword'])->name('support.users.forgotpassword')->middleware('throttle:10,1');
        Route::post('/resetpassword/update', [UsersController::class, 'resetpassword'])->name('support.users.resetpassword')->middleware('throttle:10,1');

        Route::get('/courses/{slack}', [UsersCoursesController::class, 'index'])->name('support.users.courses.index');
        Route::get('/courses/certifications/{slack}', [UsersCertificatesController::class, 'index'])->name('support.users.courses.certificates');
        Route::get('/courses/results/{slack}', [ResultsController::class, 'index'])->name('support.users.courses.results');

        Route::get('/courses/postpone/{slack}', [UsersCoursesController::class, 'postpone'])->name('support.users.courses.postpone');
        Route::delete('/courses/destroy/{user}', [UsersCoursesController::class, 'destroy'])->name('support.users.courses.destroy');
        Route::post('/courses/bulk-action', [UsersCoursesController::class, 'bulkAction'])->name('support.users.courses.bulk-action');

        Route::get('/orders/{slack}', [UsersOrdersController::class, 'index'])->name(name: 'support.users.orders.index');
        Route::post('/orders/update', [UsersOrdersController::class, 'update'])->name('support.users.orders.update');
        Route::get('/orders/view/{slack}', [UsersOrdersController::class, 'view'])->name('support.users.orders.view');
        Route::get('/orders/edit/{slack}', [UsersOrdersController::class, 'edit'])->name('support.users.orders.edit');
        Route::get('/orders/print/{slack}', [UsersOrdersController::class, 'print'])->name('support.users.orders.print');
        Route::delete('/orders/destroy/{slack}', [UsersOrdersController::class, 'destroy'])->name('support.users.orders.destroy');
        Route::post('/orders/bulk-action', [UsersOrdersController::class, 'bulkAction'])->name('support.users.orders.bulk-action');

        Route::get('/activity/{slack}', [UsersActivitysController::class, 'index'])->name('support.users.activitys');
        Route::post('/activitys/lists', [UsersActivitysController::class, 'lists'])->name('support.users.activitys.lists');
        Route::get('/activitys/view/{slack}', [UsersActivitysController::class, 'view'])->name('support.users.activitys.view');

        Route::get('/inscriptions/{slack}', [UsersInscriptionsController::class, 'index'])->name('support.users.inscriptions');
        Route::get('/inscriptions/edit/{slack}', [UsersInscriptionsController::class, 'edit'])->name('support.users.inscriptions.edit');
        Route::post('/inscriptions/action', [UsersInscriptionsController::class, 'action'])->name('support.users.inscriptions.action');

    });

});
