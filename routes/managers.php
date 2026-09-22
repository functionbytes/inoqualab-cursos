<?php

use App\Http\Controllers\Managers\ActivityLogController;
use App\Http\Controllers\Managers\Analytics\AnalyticsReportScheduleController;
use App\Http\Controllers\Managers\AnalyticsController;
use App\Http\Controllers\Managers\Blogs\BlogsController;
use App\Http\Controllers\Managers\Blogs\CategoriesController as BlogsCategoriesController;
use App\Http\Controllers\Managers\Blogs\TagsController as BlogsTagsController;
use App\Http\Controllers\Managers\BundlesController;
use App\Http\Controllers\Managers\CartAbandonmentsController;
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
use App\Http\Controllers\Managers\Invoices\InvoicesController;
use App\Http\Controllers\Managers\Invoices\ReportController as InvoicesReportController;
use App\Http\Controllers\Managers\Mailer\MailerComponentController;
use App\Http\Controllers\Managers\Mailer\MailerEndpointController;
use App\Http\Controllers\Managers\Mailer\MailerTemplateController;
use App\Http\Controllers\Managers\Mailer\MailerVariableController;
use App\Http\Controllers\Managers\MailTemplates\MailTemplatesController;
use App\Http\Controllers\Managers\NewsletterCampaignController;
use App\Http\Controllers\Managers\NewsletterController;
use App\Http\Controllers\Managers\NewsletterListController;
use App\Http\Controllers\Managers\NotificationsController;
use App\Http\Controllers\Managers\Orders\OrdersController;
use App\Http\Controllers\Managers\Orders\ReportController as OrdersReportController;
use App\Http\Controllers\Managers\Orders\ResumenController as OrdersResumenController;
use App\Http\Controllers\Managers\ProfileController;
use App\Http\Controllers\Managers\Quizs\QuizController;
use App\Http\Controllers\Managers\Quizs\TopicController as QuizTopicController;
use App\Http\Controllers\Managers\RemarketingController;
use App\Http\Controllers\Managers\Seo\GscController;
use App\Http\Controllers\Managers\Seo\SchemaOrgController;
use App\Http\Controllers\Managers\Seo\Seo404LogController;
use App\Http\Controllers\Managers\Seo\SeoAlertsController;
use App\Http\Controllers\Managers\Seo\SeoAuditController;
use App\Http\Controllers\Managers\Seo\SeoAuditHistoryController;
use App\Http\Controllers\Managers\Seo\SeoDashboardController;
use App\Http\Controllers\Managers\Seo\SeoIndexNowController;
use App\Http\Controllers\Managers\Seo\SeoLlmsController;
use App\Http\Controllers\Managers\Seo\SeoMetaController;
use App\Http\Controllers\Managers\Seo\SeoOrphanController;
use App\Http\Controllers\Managers\Seo\SeoPageUrlsController;
use App\Http\Controllers\Managers\Seo\SeoRedirectController;
use App\Http\Controllers\Managers\Seo\SeoReportController;
use App\Http\Controllers\Managers\Seo\SeoRobotsController;
use App\Http\Controllers\Managers\Seo\SeoSitemapController;
use App\Http\Controllers\Managers\Seo\SeoStaticUrlController;
use App\Http\Controllers\Managers\Seo\SeoTemplateController;
use App\Http\Controllers\Managers\Seo\WebVitalsController;
use App\Http\Controllers\Managers\Settings\AnalyticsNotificationsController;
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
use App\Http\Controllers\Managers\Settings\ModulesSettingsController;
use App\Http\Controllers\Managers\Settings\NewsletterSettingsController;
use App\Http\Controllers\Managers\Settings\PaymentsSettingsController;
use App\Http\Controllers\Managers\Settings\PixelSettingsController;
use App\Http\Controllers\Managers\Settings\PortalSettingsController;
use App\Http\Controllers\Managers\Settings\RolesController;
use App\Http\Controllers\Managers\Settings\SeoSettingsController;
use App\Http\Controllers\Managers\Settings\SettingsController;
use App\Http\Controllers\Managers\Settings\SlidersController;
use App\Http\Controllers\Managers\Settings\TestimoniesController;
use App\Http\Controllers\Managers\Settings\TrustedsController;
use App\Http\Controllers\Managers\Settings\UploadingSettingsController;
use App\Http\Controllers\Managers\Users\ActivitysController;
use App\Http\Controllers\Managers\Users\CertificatesController;
use App\Http\Controllers\Managers\Users\InscriptionsController as UsersInscriptionsController;
use App\Http\Controllers\Managers\Users\MailLogsController;
use App\Http\Controllers\Managers\Users\ResultsController;
use App\Http\Controllers\Managers\Users\UsersController;
use Illuminate\Support\Facades\Route;

// 'session' faltaba aquí (los otros 4 portales SÍ lo tienen): sin él, una
// cuenta manager deshabilitada seguía con sesión activa en /panel/* (nada
// llamaba Auth::logout()), y el mecanismo "última sesión gana" (expulsar al
// resto de dispositivos en el siguiente login) tampoco aplicaba al panel de
// mayor privilegio del sistema.
Route::group(['prefix' => 'panel', 'middleware' => ['auth', 'manager', 'session', 'panel.permission']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/data/overview', [DashboardController::class, 'overview'])->name('manager.dashboard.overview');

    // Perfil propio del manager autenticado (no requiere permiso de recurso).
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('manager.profile.edit');
        Route::put('/', [ProfileController::class, 'update'])->name('manager.profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('manager.profile.password');
    });

    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RolesController::class, 'index'])->name('manager.roles.index');
        Route::get('/matrix', [RolesController::class, 'matrix'])->name('manager.roles.matrix');
        Route::get('/create', [RolesController::class, 'create'])->name('manager.roles.create');
        Route::post('/', [RolesController::class, 'store'])->name('manager.roles.store');
        Route::get('/edit/{id}', [RolesController::class, 'edit'])->name('manager.roles.edit');
        Route::put('/{id}', [RolesController::class, 'update'])->name('manager.roles.update');
        Route::delete('/destroy/{id}', [RolesController::class, 'destroy'])->name('manager.roles.destroy');
        Route::post('/bulk-action', [RolesController::class, 'bulkAction'])->name('manager.roles.bulk-action');
    });

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
        Route::get('/data/os', [AnalyticsController::class, 'operatingSystems'])->name('manager.analytics.os');
        Route::get('/data/traffic-sources', [AnalyticsController::class, 'trafficSources'])->name('manager.analytics.traffic-sources');
        Route::get('/data/landing-pages', [AnalyticsController::class, 'landingPages'])->name('manager.analytics.landing-pages');
        Route::get('/data/exit-pages', [AnalyticsController::class, 'exitPages'])->name('manager.analytics.exit-pages');
        Route::get('/data/channel-trend', [AnalyticsController::class, 'channelTrend'])->name('manager.analytics.channel-trend');
        Route::get('/data/hourly-heatmap', [AnalyticsController::class, 'hourlyHeatmap'])->name('manager.analytics.hourly-heatmap');
        Route::get('/data/search-terms', [AnalyticsController::class, 'searchTerms'])->name('manager.analytics.search-terms');
        Route::get('/data/user-flow', [AnalyticsController::class, 'userFlow'])->name('manager.analytics.user-flow');
    });

    Route::group(['prefix' => 'notifications'], function () {

        Route::get('/', [NotificationsController::class, 'index'])->name('manager.notifications');
        Route::get('/mark-as-read', [NotificationsController::class, 'markasread'])->name('manager.notifications.markasread');
        Route::post('/bulk-action', [NotificationsController::class, 'bulkAction'])->name('manager.notifications.bulk-action');

    });

    Route::group(['prefix' => 'testimonies'], function () {
        Route::get('/', [TestimoniesController::class, 'index'])->name('manager.testimonies');
        Route::get('/create', [TestimoniesController::class, 'create'])->name('manager.testimonies.create');
        Route::post('/store', [TestimoniesController::class, 'store'])->name('manager.testimonies.store');
        Route::post('/update', [TestimoniesController::class, 'update'])->name('manager.testimonies.update');
        Route::get('/edit/{slack}', [TestimoniesController::class, 'edit'])->name('manager.testimonies.edit');
        Route::delete('/destroy/{slack}', [TestimoniesController::class, 'destroy'])->name('manager.testimonies.destroy');
        Route::post('/bulk-action', [TestimoniesController::class, 'bulkAction'])->name('manager.testimonies.bulk-action');
    });

    Route::group(['prefix' => 'departments'], function () {

        Route::get('/', [DepartmentsController::class, 'index'])->name('manager.departments');
        Route::get('/create', [DepartmentsController::class, 'create'])->name('manager.departments.create');
        Route::post('/store', [DepartmentsController::class, 'store'])->name('manager.departments.store');
        Route::post('/update', [DepartmentsController::class, 'update'])->name('manager.departments.update');
        Route::get('/edit/{slack}', [DepartmentsController::class, 'edit'])->name('manager.departments.edit');
        Route::delete('/destroy/{slack}', [DepartmentsController::class, 'destroy'])->name('manager.departments.destroy');
        Route::post('/bulk-action', [DepartmentsController::class, 'bulkAction'])->name('manager.departments.bulk-action');

    });

    Route::group(['prefix' => 'certifications'], function () {

        Route::get('/', [CertificationsController::class, 'index'])->name('manager.certifications');
        Route::get('/create', [CertificationsController::class, 'create'])->name('manager.certifications.create');
        Route::post('/store', [CertificationsController::class, 'store'])->name('manager.certifications.store');
        Route::post('/update', [CertificationsController::class, 'update'])->name('manager.certifications.update');
        Route::get('/edit/{slack}', [CertificationsController::class, 'edit'])->name('manager.certifications.edit');
        Route::delete('/destroy/{slack}', [CertificationsController::class, 'destroy'])->name('manager.certifications.destroy');
        Route::post('/bulk-action', [CertificationsController::class, 'bulkAction'])->name('manager.certifications.bulk-action');

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
        Route::delete('/destroy/{slack}', [DistributorsController::class, 'destroy'])->name('manager.distributors.destroy');
        Route::post('/bulk-action', [DistributorsController::class, 'bulkAction'])->name('manager.distributors.bulk-action');
        Route::get('/navegation/{slack}', [DistributorsController::class, 'navegation'])->name('manager.distributors.navegation');

        Route::get('/courses/{slack}', [DistributorCourseController::class, 'index'])->name('manager.distributors.courses');
        Route::post('/courses/update', [DistributorCourseController::class, 'update'])->name('manager.distributors.courses.update');

        Route::get('/enterprises/{slack}', [DistributorEnterpriseController::class, 'index'])->name('manager.distributors.enterprises');
        Route::post('/enterprises/update', [DistributorEnterpriseController::class, 'update'])->name('manager.distributors.enterprises.update');

        Route::get('/rates/{slack}', [DistributorRatesController::class, 'index'])->name('manager.distributors.rates');
        Route::post('/rates/update', [DistributorRatesController::class, 'update'])->name('manager.distributors.rates.update');

        Route::get('/staff/{slack}', [DistributorStaffController::class, 'index'])->name('manager.distributors.staffs');
        Route::post('/staff/{slack}/bulk-action', [DistributorStaffController::class, 'bulkAction'])->name('manager.distributors.staffs.bulk-action');
        Route::post('/staff/store', [DistributorStaffController::class, 'store'])->name('manager.distributors.staffs.store');
        Route::post('/staff/update', [DistributorStaffController::class, 'update'])->name('manager.distributors.staffs.update');
        Route::get('/staff/create/{slack}', [DistributorStaffController::class, 'create'])->name('manager.distributors.staffs.create');
        Route::get('/staff/edit/{slack}', [DistributorStaffController::class, 'edit'])->name('manager.distributors.staffs.edit');
        Route::get('/staff/view/{slack}', [DistributorStaffController::class, 'view'])->name('manager.distributors.staffs.view');
        Route::delete('/staff/destroy/{slack}', [DistributorStaffController::class, 'destroy'])->name('manager.distributors.staffs.destroy');

        Route::get('/invoices/{slack}', [DistributorsInvoicesController::class, 'index'])->name('manager.distributors.invoices');

    });

    Route::group(['prefix' => 'enterprises'], function () {

        Route::get('/', [EnterprisesController::class, 'index'])->name('manager.enterprises');
        Route::get('/create', [EnterprisesController::class, 'create'])->name('manager.enterprises.create');
        Route::post('/store', [EnterprisesController::class, 'store'])->name('manager.enterprises.store');
        Route::post('/update', [EnterprisesController::class, 'update'])->name('manager.enterprises.update');
        Route::get('/edit/{slack}', [EnterprisesController::class, 'edit'])->name('manager.enterprises.edit');
        Route::delete('/destroy/{slack}', [EnterprisesController::class, 'destroy'])->name('manager.enterprises.destroy');
        Route::post('/bulk-action', [EnterprisesController::class, 'bulkAction'])->name('manager.enterprises.bulk-action');
        Route::get('/navegation/{slack}', [EnterprisesController::class, 'navegation'])->name('manager.enterprises.navegation');

        Route::get('/rates/{slack}', [EnterprisesRatesController::class, 'index'])->name('manager.enterprises.rates');
        Route::post('/rates/update', [EnterprisesRatesController::class, 'update'])->name('manager.enterprises.rates.update');

        Route::get('/users/{slack}', [EnterpriseUserController::class, 'index'])->name('manager.enterprises.users');
        Route::post('/users/{slack}/bulk-action', [EnterpriseUserController::class, 'bulkAction'])->name('manager.enterprises.users.bulk-action');
        Route::post('/users/update', [EnterpriseUserController::class, 'update'])->name('manager.enterprises.users.update');
        Route::post('/users/store', [EnterpriseUserController::class, 'store'])->name('manager.enterprises.users.store');
        Route::post('/users/importation', [EnterpriseUserController::class, 'importation'])->name('manager.enterprises.users.importation');
        Route::post('/courses/importation', [EnterpriseCourseController::class, 'importation'])->name('manager.enterprises.courses.importation');

        Route::get('/users/income/generate', [EnterpriseUserController::class, 'incoming'])->name('manager.enterprises.users.incoming');
        Route::get('/users/reports/generate', [EnterpriseUserController::class, 'generate'])->name('manager.enterprises.users.generate');
        Route::post('/users/courses/action', [EnterpriseCourseController::class, 'actionUsers'])->name('manager.enterprises.courses.users');

        Route::get('/users/create/{slack}', [EnterpriseUserController::class, 'create'])->name('manager.enterprises.users.create');
        Route::get('/users/edit/{slack}', [EnterpriseUserController::class, 'edit'])->name('manager.enterprises.users.edit');
        Route::get('/users/reports/{slack}', [EnterpriseUserController::class, 'report'])->name('manager.enterprises.users.reports');
        Route::get('/users/income/{slack}', [EnterpriseUserController::class, 'income'])->name('manager.enterprises.users.income');

        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'index'])->name('manager.enterprises.courses');
        Route::post('/courses/{slack}/bulk-action', [EnterpriseCourseController::class, 'bulkAction'])->name('manager.enterprises.courses.bulk-action');
        Route::post('/courses/users/action', [EnterpriseCourseController::class, 'actionCourses'])->name('manager.enterprises.courses.action');
        Route::post('/courses/action/reasign', [EnterpriseCourseController::class, 'actionReasign'])->name('manager.enterprises.action.reasign');
        Route::post('/courses/users/include', [EnterpriseCourseController::class, 'includes'])->name('manager.enterprises.courses.include');
        Route::post('/courses/create/store', [EnterpriseCourseController::class, 'store'])->name('manager.enterprises.courses.store');

        Route::get('/courses/progress/{user}', [EnterpriseCourseController::class, 'progress'])->name('manager.enterprises.courses.progress');
        Route::get('/courses/create/{slack}', [EnterpriseCourseController::class, 'create'])->name('manager.enterprises.courses.create');
        Route::get('/courses/certificate/download/{slack}', [CertificatesController::class, 'download'])->name('manager.certificate.download');
        Route::get('/courses/users/postpone/{slack}', [EnterpriseCourseController::class, 'postponeCourses'])->name('manager.enterprises.postpone.courses');
        Route::delete('/courses/destroy/{enterprice}/{course}', [EnterpriseCourseController::class, 'destroy'])->name('manager.enterprises.courses.destroy');
        Route::get('/courses/reasign/{enterprises}/{course}', [EnterpriseCourseController::class, 'reasign'])->name('manager.enterprises.courses.reasign');
        Route::get('/courses/insert/{enterprises}/{course}', [EnterpriseCourseController::class, 'insert'])->name('manager.enterprises.courses.insert');
        Route::get('/courses/view/{enterprises}/{course}', [EnterpriseCourseController::class, 'view'])->name('manager.enterprises.courses.view');
        Route::delete('/users/courses/destroy/{slack}', [EnterpriseCourseController::class, 'destroys'])->name('manager.enterprises.courses.user.destroy');
    });

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('manager.documents');
        Route::get('/create', [DocumentsController::class, 'create'])->name('manager.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('manager.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('manager.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('manager.documents.edit');
        Route::delete('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('manager.documents.destroy');
        Route::post('/bulk-action', [DocumentsController::class, 'bulkAction'])->name('manager.documents.bulk-action');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('manager.documents.files');
        Route::delete('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('manager.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('manager.documents.files.get');

    });

    Route::group(['prefix' => 'contacts'], function () {

        Route::get('/', [ContactsController::class, 'index'])->name('manager.contacts');
        Route::post('/update', [ContactsController::class, 'update'])->name('manager.contacts.update');
        Route::get('/edit/{slack}', [ContactsController::class, 'edit'])->name('manager.contacts.edit');
        Route::delete('/destroy/{slack}', [ContactsController::class, 'destroy'])->name('manager.contacts.destroy');
        Route::post('/bulk-action', [ContactsController::class, 'bulkAction'])->name('manager.contacts.bulk-action');

    });

    Route::group(['prefix' => 'reviews'], function () {

        Route::get('/', [CourseReviewsController::class, 'index'])->name('manager.reviews');
        Route::delete('/destroy/{id}', [CourseReviewsController::class, 'destroy'])->name('manager.reviews.destroy');
        Route::post('/bulk-action', [CourseReviewsController::class, 'bulkAction'])->name('manager.reviews.bulk-action');

    });

    Route::group(['prefix' => 'certifiers'], function () {

        Route::get('/', [CertifiersController::class, 'index'])->name('manager.certifiers');
        Route::get('/create', [CertifiersController::class, 'create'])->name('manager.certifiers.create');
        Route::post('/store', [CertifiersController::class, 'store'])->name('manager.certifiers.store');
        Route::post('/update', [CertifiersController::class, 'update'])->name('manager.certifiers.update');
        Route::get('/edit/{slack}', [CertifiersController::class, 'edit'])->name('manager.certifiers.edit');
        Route::delete('/destroy/{slack}', [CertifiersController::class, 'destroy'])->name('manager.certifiers.destroy');
        Route::post('/bulk-action', [CertifiersController::class, 'bulkAction'])->name('manager.certifiers.bulk-action');

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
        Route::delete('/destroy/{slack}', [TrustedsController::class, 'destroy'])->name('manager.trusteds.destroy');
        Route::post('/bulk-action', [TrustedsController::class, 'bulkAction'])->name('manager.trusteds.bulk-action');

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
        Route::delete('/destroy/{slack}', [BundlesController::class, 'destroy'])->name('manager.bundles.destroy');
        Route::post('/bulk-action', [BundlesController::class, 'bulkAction'])->name('manager.bundles.bulk-action');
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
        Route::delete('/destroy/{slack}', [SlidersController::class, 'destroy'])->name('manager.sliders.destroy');
        Route::post('/thumbnails', [SlidersController::class, 'storeThumbnails'])->name('manager.sliders.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [SlidersController::class, 'deleteThumbnails'])->name('manager.sliders.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [SlidersController::class, 'getThumbnails'])->name('manager.sliders.thumbnails.get');
        Route::post('/bulk-action', [SlidersController::class, 'bulkAction'])->name('manager.sliders.bulk-action');

    });

    Route::group(['prefix' => 'instructions'], function () {

        Route::get('/', [InstructionsController::class, 'index'])->name('manager.instructions');
        Route::get('/create', [InstructionsController::class, 'create'])->name('manager.instructions.create');
        Route::post('/store', [InstructionsController::class, 'store'])->name('manager.instructions.store');
        Route::post('/update', [InstructionsController::class, 'update'])->name('manager.instructions.update');
        Route::get('/edit/{slack}', [InstructionsController::class, 'edit'])->name('manager.instructions.edit');
        Route::delete('/destroy/{slack}', [InstructionsController::class, 'destroy'])->name('manager.instructions.destroy');
        Route::post('/bulk-action', [InstructionsController::class, 'bulkAction'])->name('manager.instructions.bulk-action');

        Route::get('/categories', [InstructionsCategoriesController::class, 'index'])->name('manager.instructions.categories');
        Route::get('/categories/create', [InstructionsCategoriesController::class, 'create'])->name('manager.instructions.categories.create');
        Route::post('/categories/store', [InstructionsCategoriesController::class, 'store'])->name('manager.instructions.categories.store');
        Route::post('/categories/update', [InstructionsCategoriesController::class, 'update'])->name('manager.instructions.categories.update');
        Route::get('/categories/edit/{slack}', [InstructionsCategoriesController::class, 'edit'])->name('manager.instructions.categories.edit');
        Route::delete('/categories/destroy/{slack}', [InstructionsCategoriesController::class, 'destroy'])->name('manager.instructions.categories.destroy');
        Route::post('/categories/bulk-action', [InstructionsCategoriesController::class, 'bulkAction'])->name('manager.instructions.categories.bulk-action');

    });

    Route::group(['prefix' => 'faqs'], function () {

        Route::get('/', [FaqsController::class, 'index'])->name('manager.faqs');
        Route::get('/create', [FaqsController::class, 'create'])->name('manager.faqs.create');
        Route::post('/store', [FaqsController::class, 'store'])->name('manager.faqs.store');
        Route::post('/update', [FaqsController::class, 'update'])->name('manager.faqs.update');
        Route::get('/edit/{slack}', [FaqsController::class, 'edit'])->name('manager.faqs.edit');
        Route::delete('/destroy/{slack}', [FaqsController::class, 'destroy'])->name('manager.faqs.destroy');
        Route::post('/bulk-action', [FaqsController::class, 'bulkAction'])->name('manager.faqs.bulk-action');

        Route::get('/categories', [FaqsCategoriesController::class, 'index'])->name('manager.faqs.categories');
        Route::get('/categories/create', [FaqsCategoriesController::class, 'create'])->name('manager.faqs.categories.create');
        Route::post('/categories/store', [FaqsCategoriesController::class, 'store'])->name('manager.faqs.categories.store');
        Route::post('/categories/update', [FaqsCategoriesController::class, 'update'])->name('manager.faqs.categories.update');
        Route::get('/categories/edit/{slack}', [FaqsCategoriesController::class, 'edit'])->name('manager.faqs.categories.edit');
        Route::delete('/categories/destroy/{slack}', [FaqsCategoriesController::class, 'destroy'])->name('manager.faqs.categories.destroy');
        Route::post('/categories/bulk-action', [FaqsCategoriesController::class, 'bulkAction'])->name('manager.faqs.categories.bulk-action');

    });

    Route::group(['prefix' => 'blogs'], function () {

        Route::get('/', [BlogsController::class, 'index'])->name('manager.blogs');
        Route::get('/create', [BlogsController::class, 'create'])->name('manager.blogs.create');
        Route::post('/store', [BlogsController::class, 'store'])->name('manager.blogs.store');
        Route::post('/update', [BlogsController::class, 'update'])->name('manager.blogs.update');
        Route::get('/edit/{slack}', [BlogsController::class, 'edit'])->name('manager.blogs.edit');
        Route::get('/view/{slack}', [BlogsController::class, 'view'])->name('manager.blogs.view');
        Route::delete('/destroy/{slack}', [BlogsController::class, 'destroy'])->name('manager.blogs.destroy');
        Route::post('/bulk-action', [BlogsController::class, 'bulkAction'])->name('manager.blogs.bulk-action');

        Route::post('/thumbnails', [BlogsController::class, 'storeThumbnails'])->name('manager.blogs.thumbnails');
        Route::delete('/delete/thumbnails/{id}', [BlogsController::class, 'deleteThumbnails'])->name('manager.blogs.thumbnails.delete');
        Route::get('/get/thumbnails/{id}', [BlogsController::class, 'getThumbnails'])->name('manager.blogs.thumbnails.get');

        Route::get('/categories', [BlogsCategoriesController::class, 'index'])->name('manager.blogs.categories');
        Route::get('/categories/create', [BlogsCategoriesController::class, 'create'])->name('manager.blogs.categories.create');
        Route::post('/categories/store', [BlogsCategoriesController::class, 'store'])->name('manager.blogs.categories.store');
        Route::post('/categories/update', [BlogsCategoriesController::class, 'update'])->name('manager.blogs.categories.update');
        Route::get('/categories/edit/{slack}', [BlogsCategoriesController::class, 'edit'])->name('manager.blogs.categories.edit');
        Route::delete('/categories/destroy/{slack}', [BlogsCategoriesController::class, 'destroy'])->name('manager.blogs.categories.destroy');
        Route::post('/categories/bulk-action', [BlogsCategoriesController::class, 'bulkAction'])->name('manager.blogs.categories.bulk-action');

        Route::get('/tags', [BlogsTagsController::class, 'index'])->name('manager.blogs.tags');
        Route::get('/tags/create', [BlogsTagsController::class, 'create'])->name('manager.blogs.tags.create');
        Route::post('/tags/store', [BlogsTagsController::class, 'store'])->name('manager.blogs.tags.store');
        Route::post('/tags/update', [BlogsTagsController::class, 'update'])->name('manager.blogs.tags.update');
        Route::get('/tags/edit/{slack}', [BlogsTagsController::class, 'edit'])->name('manager.blogs.tags.edit');
        Route::delete('/tags/destroy/{slack}', [BlogsTagsController::class, 'destroy'])->name('manager.blogs.tags.destroy');
        Route::post('/tags/bulk-action', [BlogsTagsController::class, 'bulkAction'])->name('manager.blogs.tags.bulk-action');

    });

    Route::group(['prefix' => 'coupons'], function () {

        Route::get('/', [CouponsController::class, 'index'])->name('manager.coupons');
        Route::get('/create', [CouponsController::class, 'create'])->name('manager.coupons.create');
        Route::post('/store', [CouponsController::class, 'store'])->name('manager.coupons.store');
        Route::post('/update', [CouponsController::class, 'update'])->name('manager.coupons.update');
        Route::get('/edit/{slack}', [CouponsController::class, 'edit'])->name('manager.coupons.edit');
        Route::delete('/destroy/{slack}', [CouponsController::class, 'destroy'])->name('manager.coupons.destroy');
        Route::post('/bulk-action', [CouponsController::class, 'bulkAction'])->name('manager.coupons.bulk-action');

    });

    Route::group(['prefix' => 'orders'], function () {

        Route::get('/', [OrdersController::class, 'index'])->name('manager.orders');
        Route::post('/update', [OrdersController::class, 'update'])->name('manager.orders.update');
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
        Route::post('/store', [InvoicesController::class, 'store'])->name('manager.invoices.store');
        Route::post('/update', [InvoicesController::class, 'update'])->name('manager.invoices.update');
        Route::get('/edit/{slack}', [InvoicesController::class, 'edit'])->name('manager.invoices.edit');
        Route::get('/view/{slack}', [InvoicesController::class, 'view'])->name('manager.invoices.view');
        Route::get('/details/{slack}', [InvoicesController::class, 'details'])->name('manager.invoices.details');

        Route::get('/report', [InvoicesReportController::class, 'report'])->name('manager.invoices.report');
        Route::get('/report/generate', [InvoicesReportController::class, 'generate'])->name('manager.invoices.generate');

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
        Route::get('/maintenance/secret', [MantenanceSettingsController::class, 'secret'])->name('manager.settings.maintenance.secret');
        Route::post('/maintenance/update', [MantenanceSettingsController::class, 'update'])->name('manager.settings.maintenance.update');

        Route::get('/pixel', [PixelSettingsController::class, 'index'])->name('manager.settings.pixel');
        Route::post('/pixel/update', [PixelSettingsController::class, 'update'])->name('manager.settings.pixel.update');

        Route::get('/portal', [PortalSettingsController::class, 'index'])->name('manager.settings.portal');
        Route::post('/portal/update', [PortalSettingsController::class, 'update'])->name('manager.settings.portal.update');

        Route::get('/invoices', [InvoicesSettingsController::class, 'index'])->name('manager.settings.invoices');
        Route::post('/invoices/update', [InvoicesSettingsController::class, 'update'])->name('manager.settings.invoices.update');

        Route::get('/emails', [EmailsSettingsController::class, 'index'])->name('manager.settings.emails');
        Route::post('/emails/update', [EmailsSettingsController::class, 'update'])->name('manager.settings.emails.update');

        Route::get('/metadata', [MetaSettingsController::class, 'index'])->name('manager.settings.metadata');
        Route::post('/metadata/update', [MetaSettingsController::class, 'update'])->name('manager.settings.metadata.update');

        Route::get('/analytics', [AnalyticsSettingsController::class, 'index'])->name('manager.settings.analytics');
        Route::post('/analytics/update', [AnalyticsSettingsController::class, 'update'])->name('manager.settings.analytics.update');
        Route::post('/analytics/clear-cache', [AnalyticsSettingsController::class, 'clearCache'])->name('manager.settings.analytics.clear-cache');

        Route::get('/analytics/notifications', [AnalyticsNotificationsController::class, 'index'])->name('manager.settings.analytics.notifications');
        Route::post('/analytics/notifications/update', [AnalyticsNotificationsController::class, 'update'])->name('manager.settings.analytics.notifications.update');

        Route::prefix('/analytics/schedules')->name('manager.settings.analytics.schedules.')->group(function () {
            Route::get('/', [AnalyticsReportScheduleController::class, 'index'])->name('index');
            Route::get('/create', [AnalyticsReportScheduleController::class, 'create'])->name('create');
            Route::post('/', [AnalyticsReportScheduleController::class, 'store'])->name('store');
            Route::get('/{schedule}/edit', [AnalyticsReportScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [AnalyticsReportScheduleController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [AnalyticsReportScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/{schedule}/toggle', [AnalyticsReportScheduleController::class, 'toggle'])->name('toggle');
            Route::post('/bulk-action', [AnalyticsReportScheduleController::class, 'bulkAction'])->name('bulk-action');
        });

        Route::get('/hours', [HoursSettingsController::class, 'index'])->name('manager.settings.hours');
        Route::post('/hours/update', [HoursSettingsController::class, 'update'])->name('manager.settings.hours.update');

        Route::get('/payments', [PaymentsSettingsController::class, 'index'])->name('manager.settings.payments');
        Route::post('/payments/update', [PaymentsSettingsController::class, 'update'])->name('manager.settings.payments.update');

        Route::get('/incoming-mail', [IncomingMailSettingsController::class, 'index'])->name('manager.settings.incoming-mail');
        Route::post('/incoming-mail/update', [IncomingMailSettingsController::class, 'update'])->name('manager.settings.incoming-mail.update');

        Route::get('/newsletter', [NewsletterSettingsController::class, 'index'])->name('manager.settings.newsletter');
        Route::post('/newsletter/update', [NewsletterSettingsController::class, 'update'])->name('manager.settings.newsletter.update');

        Route::get('/modules', [ModulesSettingsController::class, 'index'])->name('manager.settings.modules');
        Route::post('/modules/update', [ModulesSettingsController::class, 'update'])->name('manager.settings.modules.update');

        Route::get('/system/uploading', [UploadingSettingsController::class, 'index'])->name('manager.settings.system.uploading');
        Route::post('/system/uploading/update', [UploadingSettingsController::class, 'update'])->name('manager.settings.system.uploading.update');

        // Nombradas manager.mails.* (no manager.settings.mails.*) a propósito:
        // EnforcePanelPermission deriva el dominio del 2º segmento del nombre
        // de ruta. Con "settings" ahí, exigía settings.create/delete -- que
        // 'support' no tiene -- en vez de incoming-mails.create/delete, que
        // el propio controller comprueba y que 'support' SÍ tiene completo.
        Route::get('/mails', [MailAutoConfirmRulesController::class, 'index'])->name('manager.mails');
        Route::post('/mails/rules', [MailAutoConfirmRulesController::class, 'store'])->name('manager.mails.rules.store');
        Route::patch('/mails/rules/{rule}/toggle', [MailAutoConfirmRulesController::class, 'toggle'])->name('manager.mails.rules.toggle');
        Route::delete('/mails/rules/{rule}', [MailAutoConfirmRulesController::class, 'destroy'])->name('manager.mails.rules.destroy');

    });

    Route::group(['prefix' => 'mails'], function () {

        Route::get('/', [ManagerIncomingMailsController::class, 'index'])->name('manager.mails.index');
        Route::get('/get-courses', [ManagerIncomingMailsController::class, 'getCourses'])->name('manager.mails.courses');
        Route::get('/get-enterprises', [ManagerIncomingMailsController::class, 'getEnterprises'])->name('manager.mails.enterprises');
        Route::get('/missing-aliases', [ManagerIncomingMailsController::class, 'missingAliases'])->name('manager.mails.missing-aliases');
        Route::post('/bulk-action', [ManagerIncomingMailsController::class, 'bulkAction'])->name('manager.mails.bulk-action');
        Route::get('/export', [ManagerIncomingMailsController::class, 'export'])->name('manager.mails.export');
        Route::post('/create-alias', [ManagerIncomingMailsController::class, 'createAlias'])->name('manager.mails.create-alias');
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
        Route::post('/update', [UsersController::class, 'update'])->name('manager.users.update');
        Route::get('/edit/{slack}', [UsersController::class, 'edit'])->name('manager.users.edit');
        Route::delete('/destroy/{slack}', [UsersController::class, 'destroy'])->name('manager.users.destroy');
        Route::post('/bulk-action', [UsersController::class, 'bulkAction'])->name('manager.users.bulk-action');
        Route::get('/activitys/{slack}', [ActivitysController::class, 'index'])->name('manager.users.activitys');

        Route::get('/courses/{slack}', [EnterpriseCourseController::class, 'user'])->name('manager.enterprises.users.courses');
        Route::get('/orders/{slack}', [UsersController::class, 'orders'])->name('manager.users.orders');
        Route::get('/certifications/{slack}', [CertificatesController::class, 'index'])->name('manager.users.certificates');
        Route::get('/results/{slack}', [ResultsController::class, 'index'])->name('manager.users.results');
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
        Route::delete('/destroy/{slack}', [CoursesController::class, 'destroy'])->name('manager.courses.destroy');
        Route::post('/bulk-action', [CoursesController::class, 'bulkAction'])->name('manager.courses.bulk-action');
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
        Route::post('/categories/bulk-action', [CoursesCategoriesController::class, 'bulkAction'])->name('manager.categories.courses.bulk-action');

        Route::get('/lessons/{slack}', [CourseLessonsController::class, 'index'])->name('manager.courses.lessons');
        Route::post('/lessons/store', [CourseLessonsController::class, 'store'])->name('manager.courses.lessons.store');
        Route::post('/lessons/update', [CourseLessonsController::class, 'update'])->name('manager.courses.lessons.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/lessons/edit/{slack}', [CourseLessonsController::class, 'edit'])->name('manager.courses.lessons.edit');
        Route::delete('/lessons/destroy/{slack}', [CourseLessonsController::class, 'destroy'])->name('manager.courses.lessons.destroy');
        Route::post('/lessons/reorder', [CourseLessonsController::class, 'reorder'])->name('manager.courses.lessons.reorder');
        Route::post('/lessons/bulk-action', [CourseLessonsController::class, 'bulkAction'])->name('manager.courses.lessons.bulk-action');

        Route::get('/announcements/{slack}', [CourseAnnouncementsController::class, 'index'])->name('manager.courses.announcements');
        Route::post('/announcements/store', [CourseAnnouncementsController::class, 'store'])->name('manager.courses.announcements.store');
        Route::post('/announcements/update', [CourseAnnouncementsController::class, 'update'])->name('manager.courses.announcements.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/announcements/edit/{slack}', [CourseAnnouncementsController::class, 'edit'])->name('manager.courses.announcements.edit');
        Route::delete('/announcements/destroy/{slack}', [CourseAnnouncementsController::class, 'destroy'])->name('manager.courses.announcements.destroy');
        Route::post('/announcements/bulk-action', [CourseAnnouncementsController::class, 'bulkAction'])->name('manager.courses.announcements.bulk-action');

        Route::get('/chapters/{slack}', [CourseChapterController::class, 'index'])->name('manager.courses.chapters');
        Route::post('/chapters/store', [CourseChapterController::class, 'store'])->name('manager.courses.chapters.store');
        Route::post('/chapters/update', [CourseChapterController::class, 'update'])->name('manager.courses.chapters.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/chapters/edit/{slack}', [CourseChapterController::class, 'edit'])->name('manager.courses.chapters.edit');
        Route::delete('/chapters/destroy/{slack}', [CourseChapterController::class, 'destroy'])->name('manager.courses.chapters.destroy');
        Route::post('/chapters/reorder', [CourseChapterController::class, 'reorder'])->name('manager.courses.chapters.reorder');
        Route::post('/chapters/bulk-action', [CourseChapterController::class, 'bulkAction'])->name('manager.courses.chapters.bulk-action');

        Route::get('/quiz/{slack}', [QuizController::class, 'index'])->name('manager.courses.quiz');
        Route::post('/quiz/store', [QuizController::class, 'store'])->name('manager.courses.quiz.store');
        Route::post('/quiz/update', [QuizController::class, 'update'])->name('manager.courses.quiz.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/quiz/edit/{slack}', [QuizController::class, 'edit'])->name('manager.courses.quiz.edit');
        Route::delete('/quiz/destroy/{slack}', [QuizController::class, 'destroy'])->name('manager.courses.quiz.destroy');
        Route::post('/quiz/bulk-action', [QuizController::class, 'bulkAction'])->name('manager.courses.quiz.bulk-action');

        Route::get('/quiz/questions/{slack}', [QuizTopicController::class, 'index'])->name('manager.courses.quiz.questions');
        Route::post('/quiz/questions/store', [QuizTopicController::class, 'store'])->name('manager.courses.quiz.questions.store');
        Route::post('/quiz/questions/update', [QuizTopicController::class, 'update'])->name('manager.courses.quiz.questions.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/quiz/questions/edit/{slack}', [QuizTopicController::class, 'edit'])->name('manager.courses.quiz.questions.edit');
        Route::delete('/quiz/questions/destroy/{slack}', [QuizTopicController::class, 'destroy'])->name('manager.courses.quiz.questions.destroy');
        Route::post('/quiz/questions/bulk-action', [QuizTopicController::class, 'bulkAction'])->name('manager.courses.quiz.questions.bulk-action');

        Route::get('/exam/{slack}', [ExamController::class, 'index'])->name('manager.courses.exam');
        Route::post('/exam/store', [ExamController::class, 'store'])->name('manager.courses.exam.store');
        Route::post('/exam/update', [ExamController::class, 'update'])->name('manager.courses.exam.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/exam/edit/{slack}', [ExamController::class, 'edit'])->name('manager.courses.exam.edit');
        Route::delete('/exam/destroy/{slack}', [ExamController::class, 'destroy'])->name('manager.courses.exam.destroy');
        Route::post('/exam/bulk-action', [ExamController::class, 'bulkAction'])->name('manager.courses.exam.bulk-action');

        Route::get('/exam/questions/{slack}', [ExamTopicController::class, 'index'])->name('manager.courses.exam.questions');
        Route::post('/exam/questions/store', [ExamTopicController::class, 'store'])->name('manager.courses.exam.questions.store');
        Route::post('/exam/questions/update', [ExamTopicController::class, 'update'])->name('manager.courses.exam.questions.update');
        // Crear/editar/eliminar se manejan en modal desde el listado; .edit devuelve JSON para poblarlo.
        Route::get('/exam/questions/edit/{slack}', [ExamTopicController::class, 'edit'])->name('manager.courses.exam.questions.edit');
        Route::delete('/exam/questions/destroy/{slack}', [ExamTopicController::class, 'destroy'])->name('manager.courses.exam.questions.destroy');
        Route::post('/exam/questions/bulk-action', [ExamTopicController::class, 'bulkAction'])->name('manager.courses.exam.questions.bulk-action');

        Route::get('/certifications/{slack}', [CertificatesController::class, 'broad'])->name('manager.certificate.broad');
        Route::get('/certificate/course/{slack}', [CertificatesController::class, 'course'])->name('manager.certificate.course');
        Route::get('/certificate/user/{slack}', [CertificatesController::class, 'user'])->name('manager.certificate.user');

        Route::get('/results/view/{slack}', [ResultsController::class, 'view'])->name('manager.results.view');
        Route::get('/results/download/{slack}', [ResultsController::class, 'download'])->name('manager.results.download');

    });

    // Audit trail (Spatie activitylog) — visor de solo lectura salvo bulk-action (purgar ruido histórico)
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('manager.activity.index');
    Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('manager.activity.export');
    Route::get('/activity/stats', [ActivityLogController::class, 'stats'])->name('manager.activity.stats');
    Route::post('/activity/bulk-action', [ActivityLogController::class, 'bulkAction'])->name('manager.activity.bulk-action');

    // ─── SEO ────────────────────────────────────────────────────────────────────

    Route::group(['prefix' => 'seo'], function () {

        // Meta tags
        Route::prefix('metas')->name('manager.seo.metas.')->group(function () {
            Route::get('/', [SeoMetaController::class, 'index'])->name('index');
            Route::get('/export', [SeoMetaController::class, 'export'])->name('export');
            Route::get('/export-json', [SeoMetaController::class, 'exportJson'])->name('export-json');
            Route::get('/import', [SeoMetaController::class, 'showImport'])->name('import');
            Route::post('/import', [SeoMetaController::class, 'import'])->name('import.process');
            Route::get('/import-json', [SeoMetaController::class, 'showImportJson'])->name('import-json');
            Route::post('/import-json', [SeoMetaController::class, 'importJson'])->name('import-json.process');
            Route::get('/{seoMeta}', [SeoMetaController::class, 'edit'])->name('edit');
            Route::put('/{seoMeta}', [SeoMetaController::class, 'update'])->name('update');
            Route::patch('/{seoMeta}/inline', [SeoMetaController::class, 'inlineUpdate'])->name('inline-update');
            Route::delete('/{seoMeta}', [SeoMetaController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-destroy', [SeoMetaController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        // Redirects
        Route::prefix('redirects')->name('manager.seo.redirects.')->group(function () {
            Route::get('/', [SeoRedirectController::class, 'index'])->name('index');
            Route::post('/', [SeoRedirectController::class, 'store'])->name('store');
            Route::post('/{seoRedirect}/test', [SeoRedirectController::class, 'test'])->name('test');
            Route::put('/{seoRedirect}', [SeoRedirectController::class, 'update'])->name('update');
            Route::delete('/{seoRedirect}', [SeoRedirectController::class, 'destroy'])->name('destroy');
            Route::post('/{seoRedirect}/toggle', [SeoRedirectController::class, 'toggleActive'])->name('toggle');
            Route::post('/bulk-destroy', [SeoRedirectController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        // 404 logs
        Route::prefix('logs')->name('manager.seo.logs.')->group(function () {
            Route::get('/', [Seo404LogController::class, 'index'])->name('index');
            Route::post('/create-redirect', [Seo404LogController::class, 'createRedirect'])->name('create-redirect');
            Route::delete('/{seo404Log}', [Seo404LogController::class, 'markResolved'])->name('mark-resolved');
            Route::post('/bulk-destroy', [Seo404LogController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::post('/clear', [Seo404LogController::class, 'clear'])->name('clear');
        });

        // Dashboard SEO
        Route::get('/dashboard', [SeoDashboardController::class, 'index'])->name('manager.seo.dashboard');
        Route::get('/analytics', [SeoDashboardController::class, 'analytics'])->name('manager.seo.analytics');
        Route::get('/search-console/import', [SeoDashboardController::class, 'showSearchConsoleImport'])->name('manager.seo.search-console.import');
        Route::post('/search-console/import', [SeoDashboardController::class, 'importSearchConsole'])->name('manager.seo.search-console.import.store');

        // Robots.txt dedicado
        Route::prefix('robots')->name('manager.seo.robots.')->group(function () {
            Route::get('/', [SeoRobotsController::class, 'index'])->name('index');
            Route::post('/', [SeoRobotsController::class, 'update'])->name('update');
            Route::post('/reset', [SeoRobotsController::class, 'reset'])->name('reset');
        });

        // llms.txt dedicado
        Route::prefix('llms')->name('manager.seo.llms.')->group(function () {
            Route::get('/', [SeoLlmsController::class, 'index'])->name('index');
            Route::post('/', [SeoLlmsController::class, 'update'])->name('update');
            Route::post('/reset', [SeoLlmsController::class, 'reset'])->name('reset');
        });

        // IndexNow
        Route::prefix('indexnow')->name('manager.seo.indexnow.')->group(function () {
            Route::get('/', [SeoIndexNowController::class, 'index'])->name('index');
            Route::post('/submit', [SeoIndexNowController::class, 'submit'])->name('submit');
        });

        // Sitemap admin
        Route::prefix('sitemap')->name('manager.seo.sitemap.')->group(function () {
            Route::get('/', [SeoSitemapController::class, 'index'])->name('index');
            Route::post('/generate', [SeoSitemapController::class, 'generate'])->name('generate');
            Route::post('/clear-cache', [SeoSitemapController::class, 'clearCache'])->name('clear-cache');
        });

        // Static URLs (sitemap manual)
        Route::prefix('static-urls')->name('manager.seo.static-urls.')->group(function () {
            Route::get('/', [SeoStaticUrlController::class, 'index'])->name('index');
            Route::get('/create', [SeoStaticUrlController::class, 'create'])->name('create');
            Route::post('/', [SeoStaticUrlController::class, 'store'])->name('store');
            Route::get('/{seoStaticUrl}/edit', [SeoStaticUrlController::class, 'edit'])->name('edit');
            Route::put('/{seoStaticUrl}', [SeoStaticUrlController::class, 'update'])->name('update');
            Route::delete('/{seoStaticUrl}', [SeoStaticUrlController::class, 'destroy'])->name('destroy');
            Route::patch('/{seoStaticUrl}/toggle', [SeoStaticUrlController::class, 'toggleActive'])->name('toggle');
            Route::post('/bulk-action', [SeoStaticUrlController::class, 'bulkAction'])->name('bulk-action');
        });

        // Page URLs
        Route::get('/page-urls', [SeoPageUrlsController::class, 'index'])->name('manager.seo.page-urls.index');

        // Orphans
        Route::prefix('orphans')->name('manager.seo.orphans.')->group(function () {
            Route::get('/', [SeoOrphanController::class, 'index'])->name('index');
            Route::post('/generate', [SeoOrphanController::class, 'generate'])->name('generate');
            Route::post('/bulk-generate', [SeoOrphanController::class, 'bulkGenerate'])->name('bulk-generate');
        });

        // Auditoría SEO
        Route::prefix('audit')->name('manager.seo.audit.')->group(function () {
            Route::get('/', [SeoAuditController::class, 'index'])->name('index');
            Route::post('/url', [SeoAuditController::class, 'auditUrl'])->middleware('throttle:10,1')->name('url');
            Route::get('/all', [SeoAuditController::class, 'auditAll'])->name('all');
            Route::get('/check-canonicals', [SeoAuditController::class, 'checkCanonicals'])->name('check-canonicals');
            Route::post('/bulk-start', [SeoAuditController::class, 'startBulkAudit'])->middleware('throttle:3,60')->name('bulk-start');
            Route::get('/bulk-progress', [SeoAuditController::class, 'bulkAuditProgress'])->name('bulk-progress');
            Route::post('/broken-links-start', [SeoAuditController::class, 'startBrokenLinksCheck'])->middleware('throttle:3,60')->name('broken-links-start');
            Route::get('/broken-links-progress', [SeoAuditController::class, 'brokenLinksProgress'])->name('broken-links-progress');
            Route::get('/internal-links', [SeoAuditController::class, 'analyzeInternalLinks'])->name('internal-links');
            Route::post('/core-web-vitals', [SeoAuditController::class, 'coreWebVitals'])->middleware('throttle:20,1')->name('core-web-vitals');
            // Historial
            Route::get('/history', [SeoAuditHistoryController::class, 'index'])->name('history');
            Route::post('/history/bulk-action', [SeoAuditHistoryController::class, 'bulkAction'])->name('history.bulk-action');
            Route::post('/history/clear', [SeoAuditHistoryController::class, 'clear'])->name('history.clear');
            Route::delete('/history/{seoAuditLog}', [SeoAuditHistoryController::class, 'destroy'])->name('history.destroy');
            Route::get('/history/{seoMeta}', [SeoAuditHistoryController::class, 'forMeta'])->name('history.meta');
        });

        // Alertas SEO
        Route::prefix('alerts')->name('manager.seo.alerts.')->group(function () {
            Route::get('/', [SeoAlertsController::class, 'index'])->name('index');
            Route::post('/{seoAlert}/acknowledge', [SeoAlertsController::class, 'acknowledge'])->name('acknowledge');
            Route::post('/acknowledge-all', [SeoAlertsController::class, 'acknowledgeAll'])->name('acknowledge-all');
            Route::delete('/{seoAlert}', [SeoAlertsController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [SeoAlertsController::class, 'bulkAction'])->name('bulk-action');
        });

        // Plantillas SEO
        Route::prefix('templates')->name('manager.seo.templates.')->group(function () {
            Route::post('/bulk-action', [SeoTemplateController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/', [SeoTemplateController::class, 'index'])->name('index');
            Route::get('/create', [SeoTemplateController::class, 'create'])->name('create');
            Route::post('/', [SeoTemplateController::class, 'store'])->name('store');
            Route::get('/{seoTemplate}/edit', [SeoTemplateController::class, 'edit'])->name('edit');
            Route::put('/{seoTemplate}', [SeoTemplateController::class, 'update'])->name('update');
            Route::delete('/{seoTemplate}', [SeoTemplateController::class, 'destroy'])->name('destroy');
            Route::patch('/{seoTemplate}/toggle-active', [SeoTemplateController::class, 'toggleActive'])->name('toggle-active');
            Route::get('/{seoTemplate}/preview', [SeoTemplateController::class, 'preview'])->name('preview');
            Route::post('/{seoTemplate}/bulk-apply', [SeoTemplateController::class, 'bulkApply'])->name('bulk-apply');
        });

        // Reporte SEO
        Route::prefix('report')->name('manager.seo.report.')->group(function () {
            Route::get('/', [SeoReportController::class, 'index'])->name('index');
            Route::get('/export', [SeoReportController::class, 'export'])->name('export');
        });

        // Core Web Vitals
        Route::prefix('web-vitals')->name('manager.seo.web-vitals.')->group(function () {
            Route::get('/', [WebVitalsController::class, 'index'])->name('index');
            Route::get('/path/{path}', [WebVitalsController::class, 'show'])->where('path', '.*')->name('show');
        });

        // Schema.org avanzado (Phase 8)
        Route::prefix('schema-org')->name('manager.seo.schema-org.')->group(function () {
            Route::post('/validate', [SchemaOrgController::class, 'validateJson'])->name('validate');
            Route::get('/template/{type}', [SchemaOrgController::class, 'template'])->name('template');
            Route::post('/bulk-apply', [SchemaOrgController::class, 'bulkApply'])->name('bulk-apply');
            Route::get('/{seoMeta}/edit', [SchemaOrgController::class, 'edit'])->name('edit');
            Route::put('/{seoMeta}', [SchemaOrgController::class, 'update'])->name('update');
        });

        // Google Search Console OAuth (Phase 9)
        Route::prefix('gsc')->name('manager.seo.gsc.')->group(function () {
            Route::get('/', [GscController::class, 'index'])->name('index');
            Route::get('/connect', [GscController::class, 'connect'])->name('connect');
            Route::get('/callback', [GscController::class, 'callback'])->name('callback');
            Route::post('/disconnect', [GscController::class, 'disconnect'])->name('disconnect');
            Route::post('/import', [GscController::class, 'import'])->name('import');
        });

    });

    // Settings SEO
    Route::prefix('settings/seo')->name('manager.settings.seo.')->group(function () {
        Route::get('/', [SeoSettingsController::class, 'index'])->name('index');
        Route::post('/', [SeoSettingsController::class, 'update'])->name('update');
        Route::post('/robots', [SeoSettingsController::class, 'updateRobots'])->name('robots');
        Route::post('/llms', [SeoSettingsController::class, 'updateLlms'])->name('llms');
    });

    // ─── Mail Templates ──────────────────────────────────────────────────────────

    Route::group(['prefix' => 'mail-templates'], function () {
        Route::get('/', [MailTemplatesController::class, 'index'])->name('manager.mail_templates');
        Route::get('/edit/{id}', [MailTemplatesController::class, 'edit'])->name('manager.mail_templates.edit');
        Route::put('/update/{id}', [MailTemplatesController::class, 'update'])->name('manager.mail_templates.update');
        Route::get('/preview/{id}', [MailTemplatesController::class, 'preview'])->name('manager.mail_templates.preview');
        Route::post('/preview-ajax/{id}', [MailTemplatesController::class, 'previewAjax'])->name('manager.mail_templates.preview_ajax');
        Route::post('/send-test/{id}', [MailTemplatesController::class, 'sendTest'])->name('manager.mail_templates.send_test');
    });

    // ─── Newsletter ───────────────────────────────────────────────────────────────

    Route::prefix('newsletter/campaigns')->name('manager.newsletter.campaigns.')->group(function () {
        Route::get('/', [NewsletterCampaignController::class, 'index'])->name('index');
        Route::get('/create', [NewsletterCampaignController::class, 'create'])->name('create');
        Route::get('/active-count', [NewsletterCampaignController::class, 'activeCount'])->name('active-count');
        Route::post('/', [NewsletterCampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}/edit', [NewsletterCampaignController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [NewsletterCampaignController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [NewsletterCampaignController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/send', [NewsletterCampaignController::class, 'send'])->name('send');
        Route::post('/{campaign}/test', [NewsletterCampaignController::class, 'test'])->name('test');
        Route::get('/{campaign}/preview', [NewsletterCampaignController::class, 'preview'])->name('preview');
        Route::post('/{campaign}/duplicate', [NewsletterCampaignController::class, 'duplicate'])->name('duplicate');
        Route::post('/{campaign}/retry', [NewsletterCampaignController::class, 'retry'])->name('retry');
        Route::post('/bulk-action', [NewsletterCampaignController::class, 'bulkAction'])->name('bulk-action');
    });

    Route::get('/newsletter/remarketing', [RemarketingController::class, 'index'])->name('manager.newsletter.remarketing');

    Route::prefix('newsletter/lists')->name('manager.newsletter.lists.')->group(function () {
        Route::get('/', [NewsletterListController::class, 'index'])->name('index');
        Route::get('/create', [NewsletterListController::class, 'create'])->name('create');
        Route::post('/', [NewsletterListController::class, 'store'])->name('store');
        Route::get('/{list}/members', [NewsletterListController::class, 'show'])->name('members');
        Route::post('/{list}/members', [NewsletterListController::class, 'addMember'])->name('members.add');
        Route::delete('/{list}/members/{newsletter}', [NewsletterListController::class, 'removeMember'])->name('members.remove');
        Route::get('/{list}/edit', [NewsletterListController::class, 'edit'])->name('edit');
        Route::put('/{list}', [NewsletterListController::class, 'update'])->name('update');
        Route::delete('/{list}', [NewsletterListController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [NewsletterListController::class, 'bulkAction'])->name('bulk-action');
    });

    Route::prefix('newsletter')->name('manager.newsletter.')->group(function () {
        Route::get('/', [NewsletterController::class, 'index'])->name('index');
        Route::post('/', [NewsletterController::class, 'store'])->name('store');
        Route::post('/import', [NewsletterController::class, 'import'])->name('import');
        Route::post('/bulk-action', [NewsletterController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [NewsletterController::class, 'export'])->name('export');
        Route::patch('/{newsletter}/toggle', [NewsletterController::class, 'toggle'])->name('toggle');
        Route::post('/{newsletter}/resend-confirmation', [NewsletterController::class, 'resendConfirmation'])->name('resend-confirmation');
        Route::delete('/{newsletter}', [NewsletterController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('cart-abandonments')->name('manager.cart-abandonments.')->group(function () {
        Route::get('/', [CartAbandonmentsController::class, 'index'])->name('index');
        Route::post('/bulk-action', [CartAbandonmentsController::class, 'bulkAction'])->name('bulk-action');
    });

    // ─── Mailer ──────────────────────────────────────────────────────────────────
    // El nombre de ruta `mailers.*` no sigue la convención `manager.*`, así que
    // EnforcePanelPermission no lo cubre. Mailer es infraestructura de Newsletter:
    // el grupo exige lectura (`newsletters.view`) y cada acción de escritura
    // exige además el verbo correspondiente (`newsletters.create/update/delete`).
    Route::prefix('settings/mailers')->name('mailers.')->middleware('can:newsletters.view')->group(function () {

        // Templates
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [MailerTemplateController::class, 'index'])->name('index');
            Route::get('/create', [MailerTemplateController::class, 'create'])->name('create');
            Route::post('/', [MailerTemplateController::class, 'store'])->name('store')->middleware('can:newsletters.create');
            Route::get('/{uid}/edit', [MailerTemplateController::class, 'edit'])->name('edit');
            Route::patch('/{uid}', [MailerTemplateController::class, 'update'])->name('update')->middleware('can:newsletters.update');
            Route::delete('/{uid}', [MailerTemplateController::class, 'destroy'])->name('destroy')->middleware('can:newsletters.delete');
            Route::get('/{uid}/preview', [MailerTemplateController::class, 'preview'])->name('preview');
            Route::post('/{uid}/preview-ajax', [MailerTemplateController::class, 'previewAjax'])->name('preview-ajax');
            Route::get('/{uid}/versions', [MailerTemplateController::class, 'versions'])->name('versions');
            Route::post('/{uid}/versions/{version}/restore', [MailerTemplateController::class, 'restoreVersion'])->name('versions.restore')->middleware('can:newsletters.update');
            Route::post('/{uid}/toggle-status', [MailerTemplateController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:newsletters.update');
            Route::post('/{uid}/send-test', [MailerTemplateController::class, 'sendTest'])->name('send-test')->middleware('can:newsletters.update');
            Route::post('/bulk-action', [MailerTemplateController::class, 'bulkAction'])->name('bulk-action')->middleware('can:newsletters.delete');
            Route::get('/{uid}/variables', [MailerTemplateController::class, 'variables'])->name('variables');
            Route::get('/variables-by-module', [MailerTemplateController::class, 'variablesByModule'])->name('variables-by-module');
        });

        // Components (Layouts)
        Route::prefix('components')->name('components.')->group(function () {
            Route::get('/', [MailerComponentController::class, 'index'])->name('index');
            Route::get('/create', [MailerComponentController::class, 'create'])->name('create');
            Route::post('/', [MailerComponentController::class, 'store'])->name('store')->middleware('can:newsletters.create');
            Route::get('/{uid}/edit', [MailerComponentController::class, 'edit'])->name('edit');
            Route::patch('/{uid}', [MailerComponentController::class, 'update'])->name('update')->middleware('can:newsletters.update');
            Route::delete('/{uid}', [MailerComponentController::class, 'destroy'])->name('destroy')->middleware('can:newsletters.delete');
            Route::post('/{uid}/duplicate', [MailerComponentController::class, 'duplicate'])->name('duplicate')->middleware('can:newsletters.create');
            Route::post('/bulk-action', [MailerComponentController::class, 'bulkAction'])->name('bulk-action')->middleware('can:newsletters.update');
            Route::get('/variables', [MailerComponentController::class, 'variables'])->name('variables');
        });

        // Variables
        Route::prefix('variables')->name('variables.')->group(function () {
            Route::get('/', [MailerVariableController::class, 'index'])->name('index');
            Route::get('/create', [MailerVariableController::class, 'create'])->name('create');
            Route::post('/', [MailerVariableController::class, 'store'])->name('store')->middleware('can:newsletters.create');
            Route::get('/{variable}/edit', [MailerVariableController::class, 'edit'])->name('edit');
            Route::patch('/{variable}', [MailerVariableController::class, 'update'])->name('update')->middleware('can:newsletters.update');
            Route::delete('/{variable}', [MailerVariableController::class, 'destroy'])->name('destroy')->middleware('can:newsletters.delete');
            Route::post('/{variable}/toggle-status', [MailerVariableController::class, 'toggleStatus'])->name('toggle-status')->middleware('can:newsletters.update');
            Route::post('/bulk-action', [MailerVariableController::class, 'bulkAction'])->name('bulk-action')->middleware('can:newsletters.update');
        });

        // Endpoints
        Route::prefix('endpoints')->name('endpoints.')->group(function () {
            Route::get('/', [MailerEndpointController::class, 'index'])->name('index');
            Route::get('/create', [MailerEndpointController::class, 'create'])->name('create');
            Route::post('/', [MailerEndpointController::class, 'store'])->name('store')->middleware('can:newsletters.create');
            Route::get('/{endpoint}/edit', [MailerEndpointController::class, 'edit'])->name('edit');
            Route::patch('/{endpoint}', [MailerEndpointController::class, 'update'])->name('update')->middleware('can:newsletters.update');
            Route::delete('/{endpoint}', [MailerEndpointController::class, 'destroy'])->name('destroy')->middleware('can:newsletters.delete');
            Route::get('/{endpoint}/logs', [MailerEndpointController::class, 'logs'])->name('logs');
            // Rotar el token invalida el actual: acción sensible, exige `update`.
            Route::post('/{endpoint}/regenerate-token', [MailerEndpointController::class, 'regenerateToken'])->name('regenerate-token')->middleware('can:newsletters.update');
            Route::post('/bulk-action', [MailerEndpointController::class, 'bulkAction'])->name('bulk-action')->middleware('can:newsletters.update');
        });

    });

});
