<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\UpgradeController;
use App\Http\Controllers\Auth\ValidationController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Managers\MigrationController;
use App\Http\Controllers\Managers\Seo\SeoWebVitalsController;
use App\Http\Controllers\Pages\BlogController;
use App\Http\Controllers\Pages\BundlesController;
use App\Http\Controllers\Pages\CartController;
use App\Http\Controllers\Pages\CertifiersController;
use App\Http\Controllers\Pages\CheckoutController;
use App\Http\Controllers\Pages\CommercialController;
use App\Http\Controllers\Pages\ContactsController;
use App\Http\Controllers\Pages\CoursesController;
use App\Http\Controllers\Pages\InstructionsController;
use App\Http\Controllers\Pages\LlmsTxtController;
use App\Http\Controllers\Pages\NewslettersController;
use App\Http\Controllers\Pages\PagesController;
use App\Http\Controllers\Pages\RobotsTxtController;
use App\Http\Controllers\Pages\UtilitiesController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {

    Route::group(['prefix' => 'migration', 'middleware' => ['auth', 'manager']], function () {

        Route::get('/users', [MigrationController::class, 'users'])->name('manager.migration.users');
        Route::get('/enterprises', [MigrationController::class, 'enterprises'])->name('manager.migration.enterprises');
        Route::get('/userenterprises', [MigrationController::class, 'userenterprises'])->name('manager.migration.userenterprises');
        Route::get('/coursesenterprises', [MigrationController::class, 'coursesenterprises'])->name('manager.migration.coursesenterprises');
        Route::get('/orders', [MigrationController::class, 'orders'])->name('manager.migration.orders');

        Route::get('/coursesprogress', [MigrationController::class, 'coursesprogress'])->name('manager.migration.coursesprogress');

    });

    Route::get('/', [PagesController::class, 'index'])->name('index');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/verified', [VerificationController::class, 'verified'])->name('verified');
    Route::get('/validation', [ValidationController::class, 'validation'])->name('validation');
    Route::get('/session-expired', function (Request $request) {
        if (auth()->check()) {
            return redirect()->route(auth()->user()->redirect());
        }

        $reason = $request->query('reason');

        seo()->noindex(true)->setTitle(
            $reason === 'device' ? 'Sesión en otro dispositivo' : 'Sesión expirada'
        );

        return view('auth.session-expired', ['reason' => $reason]);
    })->name('session.expired');
    Route::get('/about', [PagesController::class, 'about'])->name('about');
    Route::get('/home', [PagesController::class, 'home'])->name('home');
    Route::get('/faqs', [PagesController::class, 'faqs'])->name('faqs');
    Route::get('/terms', [PagesController::class, 'terms'])->name('terms');
    Route::get('/politics', [PagesController::class, 'politics'])->name('politics');
    Route::get('/cities', [UtilitiesController::class, 'getCities'])->name('cities');
    Route::get('/commercial', [CommercialController::class, 'index'])->name('commercials');
    Route::get('/coming', [PagesController::class, 'coming'])->name('coming');

    Route::controller(VerificationController::class)->group(function () {
        Route::get('/email/verify', 'show')->name('verification.notice')->middleware('auth');
        Route::get('/email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['auth', 'signed']);
        Route::post('/email/resend', 'resend')->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
    });

    Route::post('/newsletters/store', [NewslettersController::class, 'store'])->name('newsletters.store')->middleware('throttle:10,1');
    Route::get('/newsletters/confirm/{token}', [NewslettersController::class, 'confirm'])->name('newsletters.confirm');
    Route::get('/newsletters/unsubscribe/{slack}', [NewslettersController::class, 'unsubscribe'])->name('newsletters.unsubscribe');
    Route::get('/ajax/newsletter/popup', [NewslettersController::class, 'ajaxPopup'])->name('newsletters.ajax-popup')->middleware('throttle:60,1');
    Route::post('/upgrade/store', [UpgradeController::class, 'store'])->name('upgrade.store')->middleware('throttle:10,1');

    Route::get('/clear', function () {
        Artisan::call('dump-autoload');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        return '<h1>Cache Borrado</h1>';
    })->middleware(['auth', 'manager']);

    Route::group(['prefix' => 'certifiers'], function () {

        Route::get('/', [CertifiersController::class, 'index'])->name('certifiers');
        Route::get('/{slug}', [CertifiersController::class, 'view'])->name('certifiers.view');
        Route::post('/filters', [CertifiersController::class, 'filters'])->name('certifiers.filters')->middleware('throttle:30,1');
    });

    Route::group(['prefix' => 'blogs'], function () {

        Route::get('/', [BlogController::class, 'index'])->name('blogs');
        Route::get('/{slug}', [BlogController::class, 'view'])->name('blogs.view');
        Route::post('/filters', [BlogController::class, 'filters'])->name('blogs.filters')->middleware('throttle:30,1');
        Route::get('/categories/{slug}', [BlogController::class, 'categories'])->name('blogs.categories');
        Route::get('/tags/{slug}', [BlogController::class, 'tags'])->name('blogs.tags');
    });

    Route::group(['prefix' => 'courses'], function () {

        Route::get('/', [CoursesController::class, 'index'])->name('courses');
        Route::get('/{slug}', [CoursesController::class, 'view'])->name('courses.view');
        Route::get('/categories/{slug}', [CoursesController::class, 'categories'])->name('courses.categories');
    });

    Route::group(['prefix' => 'instructions'], function () {
        Route::get('/', [InstructionsController::class, 'index'])->name('instructions');
        Route::get('/{slug}', [InstructionsController::class, 'view'])->name('instructions.view');
    });

    Route::group(['prefix' => 'bundles'], function () {
        Route::get('/', [BundlesController::class, 'index'])->name('bundles');
        Route::get('/{slug}', [BundlesController::class, 'view'])->name('bundles.view');
    });

    Route::group(['prefix' => 'checkout'], function () {
        Route::get('/', [CheckoutController::class, 'checkout'])->name('checkout.cart');
        Route::get('/cities', [CheckoutController::class, 'cities'])->name('checkout.cities');
        Route::post('/register', [CheckoutController::class, 'register'])->name('checkout.register')->middleware('throttle:10,1');
        Route::post('/generate', [CheckoutController::class, 'generate'])->name('checkout.generate')->middleware('throttle:15,1');
        Route::post('/coupon/apply', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply')->middleware('throttle:15,1');
        Route::post('/coupon/clear', [CheckoutController::class, 'clearCoupon'])->name('checkout.coupon.clear');
        Route::get('/{type}/{course}', [CheckoutController::class, 'checkoutDirect'])->name('checkout');

    });

    Route::group(['prefix' => 'cart', 'middleware' => 'throttle:60,1'], function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::get('/drawer', [CartController::class, 'drawer'])->name('cart.drawer');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/update-qty', [CartController::class, 'updateQty'])->name('cart.update-qty');
        Route::post('/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('cart.clear');
    });

    Route::group(['prefix' => 'payments'], function () {
        Route::get('/processing', [CheckoutController::class, 'processing'])->name('payments.processing');
        Route::get('/response', [CheckoutController::class, 'response'])->name('payments.response')->middleware('throttle:30,1');
        Route::get('/status/{token}/{status}', [CheckoutController::class, 'status'])->name('payments.status');
        Route::get('/pay/{slack}', [CheckoutController::class, 'pay'])->name('payments.pay');
        Route::get('/sandbox/{slack}', [CheckoutController::class, 'sandbox'])->name('payments.sandbox')->middleware('throttle:20,1');
        Route::get('/simulate/{reference}/{status}', [CheckoutController::class, 'simulate'])->name('checkout.simulate')->middleware('throttle:10,1');
        Route::post('/wompi/webhook', [CheckoutController::class, 'webhook'])->name('payments.wompi.webhook');
    });

    Route::group(['prefix' => 'contacts'], function () {

        Route::get('/', [ContactsController::class, 'index'])->name('contacts');
        Route::get('/success/{slug}', [ContactsController::class, 'success'])->name('contacts.success');
        Route::post('/store', [ContactsController::class, 'storage'])->name('contacts.store');
    });

    Route::group(['prefix' => 'password'], function () {

        Route::get('/confirm', [ForgotPasswordController::class, 'showLinkRequest'])->name('password.confirm');
        Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequest'])->name('password.reset');
        Route::post('/reset', [ResetPasswordController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
        Route::post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset/{slack}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.token')->middleware('signed');

    });

    // Web Vitals beacon (público, llamado desde el browser del visitante)
    Route::post('/seo/web-vitals', [SeoWebVitalsController::class, 'store'])
        ->middleware('throttle:120,1')
        ->name('seo.web-vitals.beacon');

    // SEO — rutas públicas sin throttle agresivo
    Route::get('/robots.txt', [RobotsTxtController::class, 'serve'])->name('robots.txt');
    Route::get('/llms.txt', [LlmsTxtController::class, 'serve'])->name('llms.txt');

    // Sitemaps — públicos, throttle para bots
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
        Route::get('/sitemap-main.xml', [SitemapController::class, 'main'])->name('sitemap.main');
        Route::get('/sitemap-courses.xml', [SitemapController::class, 'courses'])->name('sitemap.courses');
        Route::get('/sitemap-blogs.xml', [SitemapController::class, 'blogs'])->name('sitemap.blogs');
    });

});
