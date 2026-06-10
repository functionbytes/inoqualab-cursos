<?php

use App\Http\Controllers\Managers\AnalyticsController;
use App\Http\Controllers\Managers\Blogs\BlogsController;
use App\Http\Controllers\Managers\Blogs\CategoriesController as BlogsCategoriesController;
use App\Http\Controllers\Managers\Blogs\TagsController as BlogsTagsController;
use App\Http\Controllers\Managers\BundlesController;
use App\Http\Controllers\Managers\CertifiersController;
use App\Http\Controllers\Managers\CouponsController;
use App\Http\Controllers\Managers\Courses\AnnouncementsController as CourseAnnouncementsController;
use App\Http\Controllers\Managers\Courses\CategoriesController as CoursesCategoriesController;
use App\Http\Controllers\Managers\Courses\ChapterController as CourseChapterController;
use App\Http\Controllers\Managers\Courses\CoursesController;
use App\Http\Controllers\Managers\Courses\LessonsController as CourseLessonsController;
use App\Http\Controllers\Managers\Courses\ReviewsController as CourseReviewsController;
use App\Http\Controllers\Managers\DashboardController;
use App\Http\Controllers\Managers\DepartmentsController;
use App\Http\Controllers\Managers\Distributors\CourseController as DistributorCourseController;
use App\Http\Controllers\Managers\Distributors\DistributorsController;
use App\Http\Controllers\Managers\Distributors\EnterpriseController as DistributorEnterpriseController;
use App\Http\Controllers\Managers\Distributors\InvoicesController as DistributorsInvoicesController;
use App\Http\Controllers\Managers\Distributors\OrdersController as DistributorsOrdersController;
use App\Http\Controllers\Managers\Distributors\RatesController as DistributorRatesController;
use App\Http\Controllers\Managers\Distributors\StaffController as DistributorStaffController;
use App\Http\Controllers\Managers\Enterprises\CourseController as EnterpriseCourseController;
use App\Http\Controllers\Managers\Enterprises\EnterprisesController;
use App\Http\Controllers\Managers\Enterprises\RatesController as EnterprisesRatesController;
use App\Http\Controllers\Managers\Enterprises\UserController as EnterpriseUserController;
use App\Http\Controllers\Managers\Exams\ExamController;
use App\Http\Controllers\Managers\Exams\TopicController as ExamTopicController;
use App\Http\Controllers\Managers\Faqs\CategoriesController as FaqsCategoriesController;
use App\Http\Controllers\Managers\Faqs\FaqsController;
use App\Http\Controllers\Managers\IncomingMails\IncomingMailsController as ManagerIncomingMailsController;
use App\Http\Controllers\Managers\IncomingMails\MailAutoConfirmRulesController;
use App\Http\Controllers\Managers\Instructions\CategoriesController as InstructionsCategoriesController;
use App\Http\Controllers\Managers\Instructions\InstructionsController;
use App\Http\Controllers\Managers\Invoices\GenerateController as InvoicesGenerateController;
use App\Http\Controllers\Managers\Invoices\InvoicesController;
use App\Http\Controllers\Managers\Invoices\ReportController as InvoicesReportController;
use App\Http\Controllers\Managers\MailTemplates\MailTemplatesController;
use App\Http\Controllers\Managers\NotificationsController;
use App\Http\Controllers\Managers\Orders\OrdersController;
use App\Http\Controllers\Managers\Orders\ReportController as OrdersReportController;
use App\Http\Controllers\Managers\Orders\ResumenController as OrdersResumenController;
use App\Http\Controllers\Managers\Quizs\QuizController;
use App\Http\Controllers\Managers\Quizs\TopicController as QuizTopicController;
use App\Http\Controllers\Managers\ReportController;
use App\Http\Controllers\Managers\Settings\AnalyticsSettingsController;
use App\Http\Controllers\Managers\Settings\CertificationsController;
use App\Http\Controllers\Managers\Settings\ContactsController;
use App\Http\Controllers\Managers\Settings\DocumentsController;
use App\Http\Controllers\Managers\Settings\EmailsSettingsController;
use App\Http\Controllers\Managers\Settings\HoursSettingsController;
use App\Http\Controllers\Managers\Settings\IncomingMailSettingsController;
use App\Http\Controllers\Managers\Settings\InvoicesSettingsController;
use App\Http\Controllers\Managers\Settings\MantenanceSettingsController;
use App\Http\Controllers\Managers\Settings\MetaSettingsController;
use App\Http\Controllers\Managers\Settings\PaymentsSettingsController;
use App\Http\Controllers\Managers\Settings\PixelSettingsController;
use App\Http\Controllers\Managers\Settings\SettingsController;
use App\Http\Controllers\Managers\Settings\SlidersController;
use App\Http\Controllers\Managers\Settings\TestimoniesController;
use App\Http\Controllers\Managers\Settings\TrustedsController;
use App\Http\Controllers\Managers\Users\ActivitysController;
use App\Http\Controllers\Managers\Users\CertificatesController;
use App\Http\Controllers\Managers\Users\InscriptionsController as UsersInscriptionsController;
use App\Http\Controllers\Managers\Users\MailLogsController;
use App\Http\Controllers\Managers\Users\ResultsController;
use App\Http\Controllers\Managers\Users\UsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager', 'middleware' => ['auth', 'manager']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('manager.dashboard');

    Route::group(['prefix' => 'analytics'], function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('manager.analytics');
        Route::get('/data/overview', [AnalyticsController::class, 'overview'])->name('manager.analytics.overview');
        Route::get('/data/comparison', [AnalyticsController::class, 'comparison'])->name('manager.analytics.comparison');
        Route::get('/data/session-metrics', [AnalyticsController::class, 'sessionMetrics'])->name('manager.analytics.session-metrics');
        Route::get('/data/top-pages', [AnalyticsController::class, 'topPages'])->name('manager.analytics.top-pages');
        Route::get('/data/top-referrers', [AnalyticsController::class, 'topReferrers'])->name('manager.analytics.top-referrers');
        Route::get('/data/browsers', [AnalyticsController::class, 'browsers'])->name('manager.analytics.browsers');
        Route::get('/data/devices', [AnalyticsController::class, 'devices'])->name('manager.analytics.devices');
        Route::get('/data/countries', [AnalyticsController::class, 'countries'])->name('manager.analytics.countries');
        Route::get('/data/channels', [AnalyticsController::class, 'channels'])->name('manager.analytics.channels');
        Route::get('/data/realtime', [AnalyticsController::class, 'realtime'])->name('manager.analytics.realtime');
    });

    Route::group(['prefix' => 'notifications'], function () {

        Route::get('/', [NotificationsController::class, 'index'])->name('manager.notifications');
        Route::get('/mark-as-read', [NotificationsController::class, 'markasread'])->name('manager.notifications.markasread');
        Route::get('/all', [NotificationsController::class, 'show'])->name('manager.notifications.markallnotify');

    });

    Route::group(['prefix' => 'testimonies'], function () {
        Route::get('/', [TestimoniesController::class, 'index'])->name('manager.testimonies');
        Route::get('/create', [TestimoniesController::class, 'create'])->name('manager.testimonies.create');
        Route::post('/store', [TestimoniesController::class, 'store'])->name('manager.testimonies.store');
        Route::post('/update', [TestimoniesController::class, 'update'])->name('manager.testimonies.update');
        Route::get('/edit/{slack}', [TestimoniesController::class, 'edit'])->name('manager.testimonies.edit');
        Route::get('/view/{slack}', [TestimoniesController::class, 'view'])->name('manager.testimonies.view');
        Route::delete('/destroy/{slack}', [TestimoniesController::class, 'destroy'])->name('manager.testimonies.destroy');
    });

    Route::group(['prefix' => 'departments'], function () {

        Route::get('/', [DepartmentsController::class, 'index'])->name('manager.departments');
        Route::get('/create', [DepartmentsController::class, 'create'])->name('manager.departments.create');
        Route::post('/store', [DepartmentsController::class, 'store'])->name('manager.departments.store');
        Route::post('/update', [DepartmentsController::class, 'update'])->name('manager.departments.update');
        Route::get('/edit/{slack}', [DepartmentsController::class, 'edit'])->name('manager.departments.edit');
        Route::get('/view/{slack}', [DepartmentsController::class, 'view'])->name('manager.departaments.view');
        Route::delete('/destroy/{slack}', [DepartmentsController::class, 'destroy'])->name('manager.departments.destroy');

    });

    Route::group(['prefix' => 'certifications'], function () {

        Route::get('/', [CertificationsController::class, 'index'])->name('manager.certifications');
        Route::get('/create', [CertificationsController::class, 'create'])->name('manager.certifications.create');
        Route::post('/store', [CertificationsController::class, 'store'])->name('manager.certifications.store');
        Route::post('/update', [CertificationsController::class, 'update'])->name('manager.certifications.update');
        Route::get('/edit/{slack}', [CertificationsController::class, 'edit'])->name('manager.certifications.edit');
        Route::delete('/destroy/{slack}', [CertificationsController::class, 'destroy'])->name('manager.certifications.destroy');

        Route::post('/thumbnails', [CertificationsController::class, 'storeThumbnails'])->name('manager.certifications.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [CertificationsController::class, 'deleteThumbnails'])->name('manager.certifications.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [CertificationsController::class, 'getThumbnails'])->name('manager.certifications.thumbnails.get');

    });

    Route::group(['prefix' => 'distributors'], function () {

        Route::get('/', [DistributorsController::class, 'index'])->name('manager.distributors');
        Route::get('/create', [DistributorsController::class, 'create'])->name('manager.distributors.create');
        Route::post('/store', [DistributorsController::class, 'store'])->name('manager.distributors.store');
        Route::post('/update', [DistributorsController::class, 'update'])->name('manager.distributors.update');
        Route::get('/edit/{slack}', [DistributorsController::class, 'edit'])->name('manager.distributors.edit');
        Route::get('/view/{slack}', [DistributorsController::class, 'view'])->name('manager.distributors.view');
        Route::delete('/destroy/{slack}', [DistributorsController::class, 'destroy'])->name('manager.distributors.destroy');
        Route::get('/navegation/{slack}', [DistributorsController::class, 'navegation'])->name('manager.distributors.navegation');

        Route::get('/courses/{slack}', [DistributorCourseController::class, 'index'])->name('manager.distributors.courses');
        Route::post('/courses/update', [DistributorCourseController::class, 'update'])->name('manager.distributors.courses.update');

        Route::get('/enterprises/{slack}', [DistributorEnterpriseController::class, 'index'])->name('manager.distributors.enterprises');
        Route::post('/enterprises/update', [DistributorEnterpriseController::class, 'update'])->name('manager.distributors.enterprises.update');

        Route::get('/rates/{slack}', [DistributorRatesController::class, 'index'])->name('manager.distributors.rates');
        Route::post('/rates/update', [DistributorRatesController::class, 'update'])->name('manager.distributors.rates.update');

        Route::get('/staff/{slack}', [DistributorStaffController::class, 'index'])->name('manager.distributors.staffs');
        Route::post('/staff/store', [DistributorStaffController::class, 'store'])->name('manager.distributors.staffs.store');
        Route::post('/staff/update', [DistributorStaffController::class, 'update'])->name('manager.distributors.staffs.update');
        Route::get('/staff/create/{slack}', [DistributorStaffController::class, 'create'])->name('manager.distributors.staffs.create');
        Route::get('/staff/edit/{slack}', [DistributorStaffController::class, 'edit'])->name('manager.distributors.staffs.edit');
        Route::get('/staff/view/{slack}', [DistributorStaffController::class, 'view'])->name('manager.distributors.staffs.view');
        Route::get('/staff/reports/{slack}', [DistributorStaffController::class, 'reports'])->name('manager.distributors.staffs.reports');
        Route::delete('/staff/destroy/{slack}', [DistributorStaffController::class, 'destroy'])->name('manager.distributors.staffs.destroy');
        Route::get('/staff/history/{slack}', [DistributorStaffController::class, 'history'])->name('manager.distributors.staffs.history');

        Route::get('/invoices/{slack}', [DistributorsInvoicesController::class, 'index'])->name('manager.distributors.invoices');
        Route::get('/orders/{slack}', [DistributorsOrdersController::class, 'index'])->name('manager.distributors.orders');

    });

    Route::group(['prefix' => 'enterprises'], function () {

        Route::get('/', [EnterprisesController::class, 'index'])->name('manager.enterprises');
        Route::get('/create', [EnterprisesController::class, 'create'])->name('manager.enterprises.create');
        Route::post('/store', [EnterprisesController::class, 'store'])->name('manager.enterprises.store');
        Route::post('/update', [EnterprisesController::class, 'update'])->name('manager.enterprises.update');
        Route::get('/edit/{slack}', [EnterprisesController::class, 'edit'])->name('manager.enterprises.edit');
        Route::get('/view/{slack}', [EnterprisesController::class, 'view'])->name('manager.enterprises.view');
        Route::delete('/destroy/{slack}', [EnterprisesController::class, 'destroy'])->name('manager.enterprises.destroy');
        Route::get('/dashboard/{slack}', [EnterprisesController::class, 'dashboard'])->name('manager.enterprises.dashboard');
        Route::get('/navegation/{slack}', [EnterprisesController::class, 'navegation'])->name('manager.enterprises.navegation');
        Route::get('/inscriptions/{slack}', [EnterprisesController::class, 'inscriptions'])->name('manager.enterprises.inscriptions');

        Route::get('/rates/{slack}', [EnterprisesRatesController::class, 'index'])->name('manager.enterprises.rates');
        Route::post('/rates/update', [EnterprisesRatesController::class, 'update'])->name('manager.enterprises.rates.update');

        Route::get('/users/{slack}', [EnterpriseUserController::class, 'index'])->name('manager.enterprises.users');
        Route::post('/users/update', [EnterpriseUserController::class, 'update'])->name('manager.enterprises.users.update');
        Route::post('/users/store', [EnterpriseUserController::class, 'store'])->name('manager.enterprises.users.store');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('manager.enterprises.reports.generate');
        Route::post('/users/importation', [EnterpriseUserController::class, 'importation'])->name('manager.enterprises.users.importation');
        Route::post('/courses/importation', [EnterpriseCourseController::class, 'importation'])->name('manager.enterprises.courses.importation');
        Route::post('/inscriptions/generate', [EnterprisesController::class, 'generate'])->name('manager.enterprises.inscriptions.generate');

        Route::get('/users/income/generate', [EnterpriseUserController::class, 'incoming'])->name('manager.enterprises.users.incoming');
        Route::get('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('manager.enterprises.users.generate');
        Route::get('/courses/reports/generate', [EnterpriseCourseController::class, 'generate'])->name('manager.enterprises.courses.generate');
        Route::post('/users/courses/action', [EnterpriseCourseController::class, 'actionUsers'])->name('manager.enterprises.courses.users');

        Route::get('/users/create/{slack}', [EnterpriseUserController::class, 'create'])->name('manager.enterprises.users.create');
        Route::get('/users/edit/{slack}', [EnterpriseUserController::class, 'edit'])->name('manager.enterprises.users.edit');
        Route::get('/users/view/{slack}', [EnterpriseUserController::class, 'view'])->name('manager.enterprises.users.view');
        Route::get('/users/postpone/{slack}', [EnterpriseCourseController::class, 'postponeUsers'])->name('manager.enterprises.postpone.users');
        Route::get('/users/reports/{slack}', [EnterpriseUserController::class, 'report'])->name('manager.enterprises.users.reports');
        Route::get('/users/import/{slack}', [EnterpriseUserController::class, 'import'])->name('manager.enterprises.users.import');
        Route::get('/users/income/{slack}', [EnterpriseUserController::class, 'income'])->name('manager.enterprises.users.income');

        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'index'])->name('manager.enterprises.courses');
        Route::post('/courses/users/action', [EnterpriseCourseController::class, 'actionCourses'])->name('manager.enterprises.courses.action');
        Route::post('/courses/action/reasign', [EnterpriseCourseController::class, 'actionReasign'])->name('manager.enterprises.action.reasign');
        Route::post('/courses/users/include', [EnterpriseCourseController::class, 'includes'])->name('manager.enterprises.courses.include');
        Route::post('/courses/create/store', [EnterpriseCourseController::class, 'store'])->name('manager.enterprises.courses.store');

        Route::get('/courses/progress/{user}', [EnterpriseCourseController::class, 'progress'])->name('manager.enterprises.courses.progress');
        Route::get('/courses/create/{slack}', [EnterpriseCourseController::class, 'create'])->name('manager.enterprises.courses.create');
        Route::get('/courses/certificate/{slack}', [CertificatesController::class, 'view'])->name('manager.certificate.view');
        Route::get('/courses/orders/edit/{slack}', [OrdersController::class, 'edit'])->name('manager.order.edit');
        Route::get('/courses/certificate/download/{slack}', [CertificatesController::class, 'download'])->name('manager.certificate.download');
        Route::get('/courses/users/postpone/{slack}', [EnterpriseCourseController::class, 'postponeCourses'])->name('manager.enterprises.postpone.courses');
        Route::delete('/courses/destroy/{enterprice}/{course}', [EnterpriseCourseController::class, 'destroy'])->name('manager.enterprises.courses.destroy');
        Route::get('/courses/reasign/{enterprises}/{course}', [EnterpriseCourseController::class, 'reasign'])->name('manager.enterprises.courses.reasign');
        Route::get('/courses/insert/{enterprises}/{course}', [EnterpriseCourseController::class, 'insert'])->name('manager.enterprises.courses.insert');
        Route::get('/courses/reports/{enterprises}/{course}', [EnterpriseCourseController::class, 'report'])->name('manager.enterprises.courses.reports');
        Route::get('/courses/notes/{enterprises}/{course}', [EnterpriseCourseController::class, 'notes'])->name('manager.enterprises.courses.notes');
        Route::get('/courses/view/{enterprises}/{course}', [EnterpriseCourseController::class, 'view'])->name('manager.enterprises.courses.view');
        Route::get('/courses/import/{enterprises}/{course}', [EnterpriseCourseController::class, 'import'])->name('manager.enterprises.courses.import');
        Route::delete('/users/courses/destroy/{user}', [EnterpriseCourseController::class, 'destroyCourse'])->name('manager.enterprises.courses.user.destroy');
    });

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('manager.documents');
        Route::get('/create', [DocumentsController::class, 'create'])->name('manager.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('manager.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('manager.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('manager.documents.edit');
        Route::get('/view/{slack}', [DocumentsController::class, 'view'])->name('manager.documents.view');
        Route::delete('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('manager.documents.destroy');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('manager.documents.files');
        Route::delete('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('manager.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('manager.documents.files.get');

    });

    Route::group(['prefix' => 'contacts'], function () {

        Route::get('/', [ContactsController::class, 'index'])->name('manager.contacts');
        Route::get('/create', [ContactsController::class, 'create'])->name('manager.contacts.create');
        Route::post('/update', [ContactsController::class, 'update'])->name('manager.contacts.update');
        Route::get('/edit/{slack}', [ContactsController::class, 'edit'])->name('manager.contacts.edit');
        Route::get('/view/{slack}', [ContactsController::class, 'view'])->name('manager.contacts.view');
        Route::delete('/destroy/{slack}', [ContactsController::class, 'destroy'])->name('manager.contacts.destroy');

    });

    Route::group(['prefix' => 'reviews'], function () {

        Route::get('/', [CourseReviewsController::class, 'index'])->name('manager.reviews');
        Route::delete('/destroy/{id}', [CourseReviewsController::class, 'destroy'])->name('manager.reviews.destroy');

    });

    Route::group(['prefix' => 'certifiers'], function () {

        Route::get('/', [CertifiersController::class, 'index'])->name('manager.certifiers');
        Route::get('/create', [CertifiersController::class, 'create'])->name('manager.certifiers.create');
        Route::post('/store', [CertifiersController::class, 'store'])->name('manager.certifiers.store');
        Route::post('/update', [CertifiersController::class, 'update'])->name('manager.certifiers.update');
        Route::get('/edit/{slack}', [CertifiersController::class, 'edit'])->name('manager.certifiers.edit');
        Route::get('/view/{slack}', [CertifiersController::class, 'view'])->name('manager.certifiers.view');
        Route::delete('/destroy/{slack}', [CertifiersController::class, 'destroy'])->name('manager.certifiers.destroy');

        Route::post('/thumbnails', [CertifiersController::class, 'storeThumbnails'])->name('manager.certifiers.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [CertifiersController::class, 'deleteThumbnails'])->name('manager.certifiers.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [CertifiersController::class, 'getThumbnails'])->name('manager.certifiers.thumbnails.get');

        Route::post('/signatures', [CertifiersController::class, 'storeSignatures'])->name('manager.certifiers.signatures');
        Route::delete('/delete/signatures/{id}', [CertifiersController::class, 'deleteSignatures'])->name('manager.certifiers.signatures.delete');
        Route::get('/get/signatures/{id}', [CertifiersController::class, 'getSignatures'])->name('manager.certifiers.signatures.get');

    });

    Route::group(['prefix' => 'trusteds'], function () {

        Route::get('/', [TrustedsController::class, 'index'])->name('manager.trusteds');
        Route::get('/create', [TrustedsController::class, 'create'])->name('manager.trusteds.create');
        Route::post('/store', [TrustedsController::class, 'store'])->name('manager.trusteds.store');
        Route::post('/update', [TrustedsController::class, 'update'])->name('manager.trusteds.update');
        Route::get('/edit/{slack}', [TrustedsController::class, 'edit'])->name('manager.trusteds.edit');
        Route::get('/view/{slack}', [TrustedsController::class, 'view'])->name('manager.trusteds.view');
        Route::delete('/destroy/{slack}', [TrustedsController::class, 'destroy'])->name('manager.trusteds.destroy');

        Route::post('/thumbnails', [TrustedsController::class, 'storeThumbnails'])->name('manager.trusteds.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [TrustedsController::class, 'deleteThumbnails'])->name('manager.trusteds.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [TrustedsController::class, 'getThumbnails'])->name('manager.trusteds.thumbnails.get');

    });

    Route::group(['prefix' => 'bundles'], function () {

        Route::get('/', [BundlesController::class, 'index'])->name('manager.bundles');
        Route::get('/create', [BundlesController::class, 'create'])->name('manager.bundles.create');
        Route::post('/store', [BundlesController::class, 'store'])->name('manager.bundles.store');
        Route::post('/update', [BundlesController::class, 'update'])->name('manager.bundles.update');
        Route::get('/edit/{slack}', [BundlesController::class, 'edit'])->name('manager.bundles.edit');
        Route::get('/view/{slack}', [BundlesController::class, 'view'])->name('manager.bundles.view');
        Route::delete('/destroy/{slack}', [BundlesController::class, 'destroy'])->name('manager.bundles.destroy');
        Route::post('/toggle-available', [BundlesController::class, 'toggleAvailable'])->name('manager.bundles.toggle');

        Route::post('/thumbnails', [BundlesController::class, 'storeThumbnails'])->name('manager.bundles.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [BundlesController::class, 'deleteThumbnails'])->name('manager.bundles.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [BundlesController::class, 'getThumbnails'])->name('manager.bundles.thumbnails.get');

    });

    Route::group(['prefix' => 'sliders'], function () {

        Route::get('/', [SlidersController::class, 'index'])->name('manager.sliders');
        Route::get('/create', [SlidersController::class, 'create'])->name('manager.sliders.create');
        Route::post('/store', [SlidersController::class, 'store'])->name('manager.sliders.store');
        Route::post('/update', [SlidersController::class, 'update'])->name('manager.sliders.update');
        Route::get('/edit/{slack}', [SlidersController::class, 'edit'])->name('manager.sliders.edit');
        Route::get('/view/{slack}', [SlidersController::class, 'view'])->name('manager.sliders.view');
        Route::delete('/destroy/{slack}', [SlidersController::class, 'destroy'])->name('manager.sliders.destroy');
        Route::post('/thumbnails', [SlidersController::class, 'storeThumbnails'])->name('manager.sliders.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [SlidersController::class, 'deleteThumbnails'])->name('manager.sliders.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [SlidersController::class, 'getThumbnails'])->name('manager.sliders.thumbnails.get');

    });

    Route::group(['prefix' => 'instructions'], function () {

        Route::get('/', [InstructionsController::class, 'index'])->name('manager.instructions');
        Route::get('/create', [InstructionsController::class, 'create'])->name('manager.instructions.create');
        Route::post('/store', [InstructionsController::class, 'store'])->name('manager.instructions.store');
        Route::post('/update', [InstructionsController::class, 'update'])->name('manager.instructions.update');
        Route::get('/edit/{slack}', [InstructionsController::class, 'edit'])->name('manager.instructions.edit');
        Route::delete('/destroy/{slack}', [InstructionsController::class, 'destroy'])->name('manager.instructions.destroy');

        Route::get('/categories', [InstructionsCategoriesController::class, 'index'])->name('manager.instructions.categories');
        Route::get('/categories/create', [InstructionsCategoriesController::class, 'create'])->name('manager.instructions.categories.create');
        Route::post('/categories/store', [InstructionsCategoriesController::class, 'store'])->name('manager.instructions.categories.store');
        Route::post('/categories/update', [InstructionsCategoriesController::class, 'update'])->name('manager.instructions.categories.update');
        Route::get('/categories/edit/{slack}', [InstructionsCategoriesController::class, 'edit'])->name('manager.instructions.categories.edit');
        Route::delete('/categories/destroy/{slack}', [InstructionsCategoriesController::class, 'destroy'])->name('manager.instructions.categories.destroy');

    });

    Route::group(['prefix' => 'faqs'], function () {

        Route::get('/', [FaqsController::class, 'index'])->name('manager.faqs');
        Route::get('/create', [FaqsController::class, 'create'])->name('manager.faqs.create');
        Route::post('/store', [FaqsController::class, 'store'])->name('manager.faqs.store');
        Route::post('/update', [FaqsController::class, 'update'])->name('manager.faqs.update');
        Route::get('/edit/{slack}', [FaqsController::class, 'edit'])->name('manager.faqs.edit');
        Route::delete('/destroy/{slack}', [FaqsController::class, 'destroy'])->name('manager.faqs.destroy');

        Route::get('/categories', [FaqsCategoriesController::class, 'index'])->name('manager.faqs.categories');
        Route::get('/categories/create', [FaqsCategoriesController::class, 'create'])->name('manager.faqs.categories.create');
        Route::post('/categories/store', [FaqsCategoriesController::class, 'store'])->name('manager.faqs.categories.store');
        Route::post('/categories/update', [FaqsCategoriesController::class, 'update'])->name('manager.faqs.categories.update');
        Route::get('/categories/edit/{slack}', [FaqsCategoriesController::class, 'edit'])->name('manager.faqs.categories.edit');
        Route::delete('/categories/destroy/{slack}', [FaqsCategoriesController::class, 'destroy'])->name('manager.faqs.categories.destroy');

    });

    Route::group(['prefix' => 'blogs'], function () {

        Route::get('/', [BlogsController::class, 'index'])->name('manager.blogs');
        Route::get('/create', [BlogsController::class, 'create'])->name('manager.blogs.create');
        Route::post('/store', [BlogsController::class, 'store'])->name('manager.blogs.store');
        Route::post('/update', [BlogsController::class, 'update'])->name('manager.blogs.update');
        Route::get('/edit/{slack}', [BlogsController::class, 'edit'])->name('manager.blogs.edit');
        Route::get('/view/{slack}', [BlogsController::class, 'view'])->name('manager.blogs.view');
        Route::delete('/destroy/{slack}', [BlogsController::class, 'destroy'])->name('manager.blogs.destroy');

        Route::post('/thumbnails', [BlogsController::class, 'storeThumbnails'])->name('manager.blogs.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [BlogsController::class, 'deleteThumbnails'])->name('manager.blogs.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [BlogsController::class, 'getThumbnails'])->name('manager.blogs.thumbnails.get');

        Route::get('/categories', [BlogsCategoriesController::class, 'index'])->name('manager.blogs.categories');
        Route::get('/categories/create', [BlogsCategoriesController::class, 'create'])->name('manager.blogs.categories.create');
        Route::post('/categories/store', [BlogsCategoriesController::class, 'store'])->name('manager.blogs.categories.store');
        Route::post('/categories/update', [BlogsCategoriesController::class, 'update'])->name('manager.blogs.categories.update');
        Route::get('/categories/edit/{slack}', [BlogsCategoriesController::class, 'edit'])->name('manager.blogs.categories.edit');
        Route::get('/categories/view/{slack}', [BlogsCategoriesController::class, 'view'])->name('manager.blogs.categories.view');
        Route::delete('/categories/destroy/{slack}', [BlogsCategoriesController::class, 'destroy'])->name('manager.blogs.categories.destroy');

        Route::get('/tags', [BlogsTagsController::class, 'index'])->name('manager.blogs.tags');
        Route::get('/tags/create', [BlogsTagsController::class, 'create'])->name('manager.blogs.tags.create');
        Route::post('/tags/store', [BlogsTagsController::class, 'store'])->name('manager.blogs.tags.store');
        Route::post('/tags/update', [BlogsTagsController::class, 'update'])->name('manager.blogs.tags.update');
        Route::get('/tags/edit/{slack}', [BlogsTagsController::class, 'edit'])->name('manager.blogs.tags.edit');
        Route::get('/tags/view/{slack}', [BlogsTagsController::class, 'view'])->name('manager.blogs.tags.view');
        Route::delete('/tags/destroy/{slack}', [BlogsTagsController::class, 'destroy'])->name('manager.blogs.tags.destroy');

    });

    Route::group(['prefix' => 'coupons'], function () {

        Route::get('/', [CouponsController::class, 'index'])->name('manager.coupons');
        Route::get('/create', [CouponsController::class, 'create'])->name('manager.coupons.create');
        Route::post('/store', [CouponsController::class, 'store'])->name('manager.coupons.store');
        Route::post('/update', [CouponsController::class, 'update'])->name('manager.coupons.update');
        Route::get('/edit/{slack}', [CouponsController::class, 'edit'])->name('manager.coupons.edit');
        Route::get('/view/{slack}', [CouponsController::class, 'view'])->name('manager.coupons.view');
        Route::delete('/destroy/{slack}', [CouponsController::class, 'destroy'])->name('manager.coupons.destroy');

    });

    Route::group(['prefix' => 'orders'], function () {

        Route::get('/', [OrdersController::class, 'index'])->name('manager.orders');
        Route::get('/get', [OrdersController::class, 'get'])->name('manager.orders.get');
        Route::get('/create', [OrdersController::class, 'create'])->name('manager.orders.create');
        Route::post('/store', [OrdersController::class, 'store'])->name('manager.orders.store');
        Route::post('/update', [OrdersController::class, 'update'])->name('manager.orders.update');
        Route::get('/print/{slack}', [OrdersController::class, 'print'])->name('manager.orders.print');
        Route::get('/edit/{slack}', [OrdersController::class, 'edit'])->name('manager.orders.edit');
        Route::get('/view/{slack}', [OrdersController::class, 'view'])->name('manager.orders.view');
        Route::delete('/destroy/{slack}', [OrdersController::class, 'destroy'])->name('manager.orders.destroy');

        Route::get('/report', [OrdersReportController::class, 'report'])->name('manager.orders.report');
        Route::get('/report/generate', [OrdersReportController::class, 'generate'])->name('manager.orders.generate');
        Route::post('/report/get/enterprises', [OrdersReportController::class, 'getEnterprises'])->name('manager.orders.get.enterprises');

        Route::get('/resumen', [OrdersResumenController::class, 'resumen'])->name('manager.orders.resumen');
        Route::get('/resumen/generate', [OrdersResumenController::class, 'generate'])->name('manager.orders.resumen.generate');
        Route::post('/resumen/get/enterprises', [OrdersResumenController::class, 'getEnterprises'])->name('manager.orders.resumen.get.enterprises');

    });

    Route::group(['prefix' => 'invoices'], function () {

        Route::get('/', [InvoicesController::class, 'index'])->name('manager.invoices');
        Route::get('/get', [InvoicesController::class, 'get'])->name('manager.invoices.get');
        Route::get('/create', [InvoicesController::class, 'create'])->name('manager.invoices.create');
        Route::post('/store', [InvoicesController::class, 'store'])->name('manager.invoices.store');
        Route::post('/update', [InvoicesController::class, 'update'])->name('manager.invoices.update');
        Route::get('/print/{slack}', [InvoicesController::class, 'print'])->name('manager.invoices.print');
        Route::get('/edit/{slack}', [InvoicesController::class, 'edit'])->name('manager.invoices.edit');
        Route::get('/view/{slack}', [InvoicesController::class, 'view'])->name('manager.invoices.view');
        Route::get('/details/{slack}', [InvoicesController::class, 'details'])->name('manager.invoices.details');

        Route::delete('/destroy/{slack}', [InvoicesController::class, 'destroy'])->name('manager.invoices.destroy');

        Route::get('/report', [InvoicesReportController::class, 'report'])->name('manager.invoices.report');
        Route::get('/report/generate', [InvoicesReportController::class, 'generate'])->name('manager.invoices.generate');
        Route::get('/pdf/{slack}', [InvoicesGenerateController::class, 'generate'])->name('manager.invoices.pdf');

    });

    Route::group(['prefix' => 'settings'], function () {

        Route::get('/', [SettingsController::class, 'index'])->name('manager.settings');
        Route::post('/update', [SettingsController::class, 'update'])->name('manager.settings.update');

        Route::post('/favicon', [SettingsController::class, 'storeFavicon'])->name('manager.settings.favicon');
        Route::delete('/delete/favicon/{id}', [SettingsController::class, 'deleteFavicon'])->name('manager.settings.favicon.delete');
        Route::get('/get/favicon/{id}', [SettingsController::class, 'getFavicon'])->name('manager.settings.favicon.get');

        Route::post('/logo', [SettingsController::class, 'storeLogo'])->name('manager.settings.logo');
        Route::delete('/delete/logo/{id}', [SettingsController::class, 'deleteLogo'])->name('manager.settings.logo.delete');
        Route::get('/get/logo/{id}', [SettingsController::class, 'getLogo'])->name('manager.settings.logo.get');

        Route::post('/metadata', [MetaSettingsController::class, 'storeMetas'])->name('manager.settings.metadata.store');
        Route::delete('/delete/metadata/{id}', [MetaSettingsController::class, 'deleteMetas'])->name('manager.settings.metadata.delete');
        Route::get('/get/metadata/{id}', [MetaSettingsController::class, 'getMetas'])->name('manager.settings.metadata.get');

        Route::get('/maintenance', [MantenanceSettingsController::class, 'index'])->name('manager.settings.maintenance');
        Route::post('/maintenance/update', [MantenanceSettingsController::class, 'update'])->name('manager.settings.maintenance.update');

        Route::get('/pixel', [PixelSettingsController::class, 'index'])->name('manager.settings.pixel');
        Route::post('/pixel/update', [PixelSettingsController::class, 'update'])->name('manager.settings.pixel.update');

        Route::get('/invoices', [InvoicesSettingsController::class, 'index'])->name('manager.settings.invoices');
        Route::post('/invoices/update', [InvoicesSettingsController::class, 'update'])->name('manager.settings.invoices.update');

        Route::get('/emails', [EmailsSettingsController::class, 'index'])->name('manager.settings.emails');
        Route::post('/emails/update', [EmailsSettingsController::class, 'update'])->name('manager.settings.emails.update');

        Route::get('/metadata', [MetaSettingsController::class, 'index'])->name('manager.settings.metadata');
        Route::post('/metadata/update', [MetaSettingsController::class, 'update'])->name('manager.settings.metadata.update');

        Route::get('/analytics', [AnalyticsSettingsController::class, 'index'])->name('manager.settings.analytics');
        Route::post('/analytics/update', [AnalyticsSettingsController::class, 'update'])->name('manager.settings.analytics.update');

        Route::get('/hours', [HoursSettingsController::class, 'index'])->name('manager.settings.hours');
        Route::post('/hours/update', [HoursSettingsController::class, 'update'])->name('manager.settings.hours.update');

        Route::get('/payments', [PaymentsSettingsController::class, 'index'])->name('manager.settings.payments');
        Route::post('/payments/update', [PaymentsSettingsController::class, 'update'])->name('manager.settings.payments.update');

        Route::get('/incoming-mail', [IncomingMailSettingsController::class, 'index'])->name('manager.settings.incoming-mail');
        Route::post('/incoming-mail/update', [IncomingMailSettingsController::class, 'update'])->name('manager.settings.incoming-mail.update');

        Route::get('/mails', [MailAutoConfirmRulesController::class, 'index'])->name('manager.settings.mails');
        Route::post('/mails/rules', [MailAutoConfirmRulesController::class, 'store'])->name('manager.settings.mails.rules.store');
        Route::patch('/mails/rules/{id}/toggle', [MailAutoConfirmRulesController::class, 'toggle'])->name('manager.settings.mails.rules.toggle');
        Route::delete('/mails/rules/{id}', [MailAutoConfirmRulesController::class, 'destroy'])->name('manager.settings.mails.rules.destroy');

    });

    Route::group(['prefix' => 'mails'], function () {

        Route::get('/', [ManagerIncomingMailsController::class, 'index'])->name('manager.mails.index');
        Route::get('/get-courses', [ManagerIncomingMailsController::class, 'getCourses'])->name('manager.mails.courses');
        Route::get('/get-enterprises', [ManagerIncomingMailsController::class, 'getEnterprises'])->name('manager.mails.enterprises');
        Route::get('/missing-aliases', [ManagerIncomingMailsController::class, 'missingAliases'])->name('manager.mails.missing-aliases');
        Route::post('/bulk-action', [ManagerIncomingMailsController::class, 'bulkAction'])->name('manager.mails.bulk-action');
        Route::get('/export', [ManagerIncomingMailsController::class, 'export'])->name('manager.mails.export');
        Route::post('/create-alias', [ManagerIncomingMailsController::class, 'createAlias'])->name('manager.mails.create-alias');
        Route::get('/pending-count', [ManagerIncomingMailsController::class, 'pendingCount'])->name('manager.mails.pending-count');
        Route::get('/get-reviewers', [ManagerIncomingMailsController::class, 'getReviewers'])->name('manager.mails.reviewers');
        Route::get('/{slack}', [ManagerIncomingMailsController::class, 'show'])->name('manager.mails.show');
        Route::get('/{slack}/preview', [ManagerIncomingMailsController::class, 'preview'])->name('manager.mails.preview');
        Route::post('/{slack}/confirm', [ManagerIncomingMailsController::class, 'confirm'])->name('manager.mails.confirm');
        Route::post('/{slack}/discard', [ManagerIncomingMailsController::class, 'discard'])->name('manager.mails.discard');
        Route::post('/{slack}/reparse', [ManagerIncomingMailsController::class, 'reparse'])->name('manager.mails.reparse');
        Route::post('/{slack}/note', [ManagerIncomingMailsController::class, 'saveNote'])->name('manager.mails.note');
        Route::post('/{slack}/assign', [ManagerIncomingMailsController::class, 'assign'])->name('manager.mails.assign');

    });

    Route::group(['prefix' => 'users'], function () {

        Route::get('/', [UsersController::class, 'index'])->name('manager.users');
        Route::get('/create', [UsersController::class, 'create'])->name('manager.users.create');
        Route::post('/store', [UsersController::class, 'store'])->name('manager.users.store');
        Route::post('/filters', [UsersController::class, 'filters'])->name('manager.users.filters');
        Route::post('/update', [UsersController::class, 'update'])->name('manager.users.update');
        Route::get('/edit/{slack}', [UsersController::class, 'edit'])->name('manager.users.edit');
        Route::get('/view/{slack}', [UsersController::class, 'view'])->name('manager.users.view');
        Route::delete('/destroy/{slack}', [UsersController::class, 'destroy'])->name('manager.users.destroy');
        Route::get('/dashboard/{slack}', [UsersController::class, 'dashboard'])->name('manager.users.dashboard');
        Route::get('/activitys/{slack}', [ActivitysController::class, 'index'])->name('manager.users.activitys');
        Route::post('/reports/generate', [UsersController::class, 'generate'])->name('manager.users.generate');

        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'user'])->name('manager.enterprises.users.courses');
        Route::get('/orders/{slack}', [UsersController::class, 'orders'])->name('manager.users.orders');
        Route::get('/inscriptions/{slack}', [UsersController::class, 'inscriptions'])->name('manager.users.inscriptions');
        Route::get('/certifications/{slack}', [CertificatesController::class, 'index'])->name('manager.enterprises.users.certificates');
        Route::get('/results/{slack}', [ResultsController::class, 'index'])->name('manager.enterprises.users.results');
        Route::post('/activitys/lists', [ActivitysController::class, 'lists'])->name('manager.enterprises.users.lists');
        Route::get('/activitys/view/{slack}', [ActivitysController::class, 'view'])->name('manager.enterprises.activitys.view');

        Route::delete('/orders/destroy/{slack}', [UsersController::class, 'destroyOrders'])->name('manager.enterprises.users.orders.destroy');

        Route::get('/emails/{slack}', [MailLogsController::class, 'index'])->name('manager.users.emails');
        Route::get('/emails/log/{id}', [MailLogsController::class, 'show'])->name('manager.users.emails.show');

        Route::get('/inscriptions/{slack}', [UsersInscriptionsController::class, 'index'])->name('manager.users.inscriptions');
        Route::get('/inscriptions/edit/{slack}', [UsersInscriptionsController::class, 'edit'])->name('manager.users.inscriptions.edit');
        Route::post('/inscriptions/action', [UsersInscriptionsController::class, 'action'])->name('manager.users.inscriptions.action');

    });

    Route::group(['prefix' => 'courses'], function () {

        Route::get('/', [CoursesController::class, 'index'])->name('manager.courses');
        Route::get('/create', [CoursesController::class, 'create'])->name('manager.courses.create');
        Route::post('/store', [CoursesController::class, 'store'])->name('manager.courses.store');
        Route::post('/update', [CoursesController::class, 'update'])->name('manager.courses.update');
        Route::post('/duplicate/action', [CoursesController::class, 'action'])->name('manager.courses.action');
        Route::get('/duplicate/{slack}', [CoursesController::class, 'duplicate'])->name('manager.courses.duplicate');
        Route::get('/edit/{slack}', [CoursesController::class, 'edit'])->name('manager.courses.edit');
        Route::get('/view/{slack}', [CoursesController::class, 'view'])->name('manager.courses.view');
        Route::delete('/destroy/{slack}', [CoursesController::class, 'destroy'])->name('manager.courses.destroy');
        Route::get('/navegation/{slack}', [CoursesController::class, 'navegation'])->name('manager.courses.navegation');

        Route::post('/thumbnails', [CoursesController::class, 'storeThumbnails'])->name('manager.courses.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [CoursesController::class, 'deleteThumbnails'])->name('manager.courses.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [CoursesController::class, 'getThumbnails'])->name('manager.courses.thumbnails.get');

        Route::get('/categories', [CoursesCategoriesController::class, 'index'])->name('manager.categories.courses');
        Route::get('/categories/create', [CoursesCategoriesController::class, 'create'])->name('manager.categories.courses.create');
        Route::post('/categories/store', [CoursesCategoriesController::class, 'store'])->name('manager.categories.courses.store');
        Route::post('/categories/update', [CoursesCategoriesController::class, 'update'])->name('manager.categories.courses.update');
        Route::get('/categories/edit/{slack}', [CoursesCategoriesController::class, 'edit'])->name('manager.categories.courses.edit');
        Route::get('/categories/view/{slack}', [CoursesCategoriesController::class, 'view'])->name('manager.categories.courses.view');
        Route::delete('/categories/destroy/{slack}', [CoursesCategoriesController::class, 'destroy'])->name('manager.categories.courses.destroy');

        Route::get('/lessons/{slack}', [CourseLessonsController::class, 'index'])->name('manager.courses.lessons');
        Route::get('/lessons/create/{slack}', [CourseLessonsController::class, 'create'])->name('manager.courses.lessons.create');
        Route::post('/lessons/store', [CourseLessonsController::class, 'store'])->name('manager.courses.lessons.store');
        Route::post('/lessons/update', [CourseLessonsController::class, 'update'])->name('manager.courses.lessons.update');
        Route::get('/lessons/edit/{slack}', [CourseLessonsController::class, 'edit'])->name('manager.courses.lessons.edit');
        Route::delete('/lessons/destroy/{slack}', [CourseLessonsController::class, 'destroy'])->name('manager.courses.lessons.destroy');

        Route::get('/announcements/{slack}', [CourseAnnouncementsController::class, 'index'])->name('manager.courses.announcements');
        Route::get('/announcements/create/{slack}', [CourseAnnouncementsController::class, 'create'])->name('manager.courses.announcements.create');
        Route::post('/announcements/store', [CourseAnnouncementsController::class, 'store'])->name('manager.courses.announcements.store');
        Route::post('/announcements/update', [CourseAnnouncementsController::class, 'update'])->name('manager.courses.announcements.update');
        Route::get('/announcements/edit/{slack}', [CourseAnnouncementsController::class, 'edit'])->name('manager.courses.announcements.edit');
        Route::delete('/announcements/destroy/{slack}', [CourseAnnouncementsController::class, 'destroy'])->name('manager.courses.announcements.destroy');

        Route::get('/chapters/{slack}', [CourseChapterController::class, 'index'])->name('manager.courses.chapters');
        Route::get('/chapters/create/{slack}', [CourseChapterController::class, 'create'])->name('manager.courses.chapters.create');
        Route::post('/chapters/store', [CourseChapterController::class, 'store'])->name('manager.courses.chapters.store');
        Route::post('/chapters/update', [CourseChapterController::class, 'update'])->name('manager.courses.chapters.update');
        Route::get('/chapters/edit/{slack}', [CourseChapterController::class, 'edit'])->name('manager.courses.chapters.edit');
        Route::delete('/chapters/destroy/{slack}', [CourseChapterController::class, 'destroy'])->name('manager.courses.chapters.destroy');

        Route::get('/quiz/{slack}', [QuizController::class, 'index'])->name('manager.courses.quiz');
        Route::get('/quiz/create/{slack}', [QuizController::class, 'create'])->name('manager.courses.quiz.create');
        Route::post('/quiz/store', [QuizController::class, 'store'])->name('manager.courses.quiz.store');
        Route::post('/quiz/update', [QuizController::class, 'update'])->name('manager.courses.quiz.update');
        Route::get('/quiz/edit/{slack}', [QuizController::class, 'edit'])->name('manager.courses.quiz.edit');
        Route::delete('/quiz/destroy/{slack}', [QuizController::class, 'destroy'])->name('manager.courses.quiz.destroy');

        Route::get('/quiz/questions/{slack}', [QuizTopicController::class, 'index'])->name('manager.courses.quiz.questions');
        Route::get('/quiz/questions/create/{slack}', [QuizTopicController::class, 'create'])->name('manager.courses.quiz.questions.create');
        Route::post('/quiz/questions/store', [QuizTopicController::class, 'store'])->name('manager.courses.quiz.questions.store');
        Route::post('/quiz/questions/update', [QuizTopicController::class, 'update'])->name('manager.courses.quiz.questions.update');
        Route::get('/quiz/questions/edit/{slack}', [QuizTopicController::class, 'edit'])->name('manager.courses.quiz.questions.edit');
        Route::delete('/quiz/questions/destroy/{slack}', [QuizTopicController::class, 'destroy'])->name('manager.courses.quiz.questions.destroy');

        Route::get('/exam/{slack}', [ExamController::class, 'index'])->name('manager.courses.exam');
        Route::get('/exam/create/{slack}', [ExamController::class, 'create'])->name('manager.courses.exam.create');
        Route::post('/exam/store', [ExamController::class, 'store'])->name('manager.courses.exam.store');
        Route::post('/exam/update', [ExamController::class, 'update'])->name('manager.courses.exam.update');
        Route::get('/exam/edit/{slack}', [ExamController::class, 'edit'])->name('manager.courses.exam.edit');
        Route::delete('/exam/destroy/{slack}', [ExamController::class, 'destroy'])->name('manager.courses.exam.destroy');

        Route::get('/exam/questions/{slack}', [ExamTopicController::class, 'index'])->name('manager.courses.exam.questions');
        Route::get('/exam/questions/create/{slack}', [ExamTopicController::class, 'create'])->name('manager.courses.exam.questions.create');
        Route::post('/exam/questions/store', [ExamTopicController::class, 'store'])->name('manager.courses.exam.questions.store');
        Route::post('/exam/questions/update', [ExamTopicController::class, 'update'])->name('manager.courses.exam.questions.update');
        Route::get('/exam/questions/edit/{slack}', [ExamTopicController::class, 'edit'])->name('manager.courses.exam.questions.edit');
        Route::delete('/exam/questions/destroy/{slack}', [ExamTopicController::class, 'destroy'])->name('manager.courses.exam.questions.destroy');

        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('manager.courses.generate');

        Route::get('/certifications/{slack}', [CertificatesController::class, 'broad'])->name('manager.enterprises.certificate.broad');
        Route::get('/certificate/course/{slack}', [CertificatesController::class, 'course'])->name('manager.enterprises.certificate.course');
        Route::get('/certificate/user/{slack}', [CertificatesController::class, 'user'])->name('manager.enterprises.certificate.user');

        Route::get('/results/view/{slack}', [ResultsController::class, 'view'])->name('manager.enterprises.results.view');
        Route::get('/results/download/{slack}', [ResultsController::class, 'download'])->name('manager.enterprises.results.download');

    });

    Route::group(['prefix' => 'mail-templates'], function () {
        Route::get('/', [MailTemplatesController::class, 'index'])->name('manager.mail_templates');
        Route::get('/edit/{id}', [MailTemplatesController::class, 'edit'])->name('manager.mail_templates.edit');
        Route::put('/update/{id}', [MailTemplatesController::class, 'update'])->name('manager.mail_templates.update');
        Route::get('/preview/{id}', [MailTemplatesController::class, 'preview'])->name('manager.mail_templates.preview');
        Route::post('/preview-ajax/{id}', [MailTemplatesController::class, 'previewAjax'])->name('manager.mail_templates.preview_ajax');
        Route::post('/send-test/{id}', [MailTemplatesController::class, 'sendTest'])->name('manager.mail_templates.send_test');
    });

});
