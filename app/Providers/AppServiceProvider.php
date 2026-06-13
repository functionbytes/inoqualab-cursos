<?php

namespace App\Providers;

use App\Html\FormBuilder;
use App\Html\HtmlBuilder;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use App\Models\Mailer\MailerVariable;
use App\Models\Setting\Setting;
use App\Models\User;
use App\Observers\Mailer\MailerLayoutObserver;
use App\Observers\Mailer\MailerTemplateObserver;
use App\Observers\Mailer\MailerVariableObserver;
use App\Observers\User\UserRoleObserver;
use App\Services\SchemaOrgService;
use App\Services\SeoService;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('html.builder', fn () => new HtmlBuilder);
        $this->app->singleton('form.builder', fn () => new FormBuilder);
        $this->app->singleton(SeoService::class);
        $this->app->singleton(SchemaOrgService::class);

        view()->composer('*', function ($view) {
            if (app()->bound('distributor')) {
                $view->with('distributor', app('distributor'));
            }
        });

        view()->composer('*', function ($view) {
            $view->with('about', $this->about());
        });

        view()->composer('*', function ($view) {
            $view->with('setting', $this->setting());
        });

        view()->composer('*', function ($view) {
            $view->with('allcourses', $this->courses());
        });

        view()->composer('*', function ($view) {
            $view->with('hasActiveBundles', $this->hasActiveBundles());
        });

        view()->composer('*', function ($view) {
            if (Auth::user() != null) {
                // $user = User::auth();
                // $purchases = Course::purchases($user->id);
                // $view->with("purchases", $purchases);
            } else {

                // $purchases = [];
                // $view->with("purchases", $purchases);
            }
        });

    }

    private static ?object $settingCache = null;

    private static ?object $coursesCache = null;

    private static ?bool $hasActiveBundlesCache = null;

    public function about(): ?object
    {
        return static::$settingCache ??= Setting::first();
    }

    public function setting(): ?object
    {
        return static::$settingCache ??= Setting::first();
    }

    public function courses(): object
    {
        return static::$coursesCache ??= Course::available()->website()->orderBy('title', 'asc')->get();
    }

    public function hasActiveBundles(): bool
    {
        return static::$hasActiveBundlesCache ??= Bundle::available()->exists();
    }

    public function boot()
    {
        Schema::defaultStringLength(191);

        Blade::directive('seoTags', fn () => '<?php echo app(\App\Services\SeoService::class)->render(); ?>');
        Blade::directive('schemaOrg', fn () => '<?php if ($schema = app(\App\Services\SchemaOrgService::class)->organization()): echo \'<script type="application/ld+json">\'.json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).\'</script>\'; endif; ?>');

        Queue::before(function (JobProcessing $event) {
            // $event->connectionName
            // $event->job
            // $event->job->payload()
        });

        Queue::after(function (JobProcessed $event) {
            // $event->connectionName
            // $event->job
            // $event->job->payload()
        });

        Queue::failing(function (JobFailed $event) {
            // $event->connectionName
            // $event->job
            // $event->exception
        });

        // Mailer observers
        MailerTemplate::observe(MailerTemplateObserver::class);
        MailerLayout::observe(MailerLayoutObserver::class);
        MailerVariable::observe(MailerVariableObserver::class);

        // Sincroniza la columna `role` (legacy) con los roles de Spatie.
        User::observe(UserRoleObserver::class);

        $this->registerPolicies();
    }

    /**
     * Registro explícito de Policies (los modelos viven en sub-namespaces,
     * por lo que el auto-discovery convencional no los resuelve).
     */
    private function registerPolicies(): void
    {
        Gate::policy(\App\Models\Order\Order::class, \App\Policies\OrderPolicy::class);
        Gate::policy(\App\Models\Invoice\Invoice::class, \App\Policies\InvoicePolicy::class);
        Gate::policy(\App\Models\Inscription::class, \App\Policies\InscriptionPolicy::class);
        Gate::policy(\App\Models\Users\Certificate::class, \App\Policies\CertificatePolicy::class);
        Gate::policy(\App\Models\Course\Course::class, \App\Policies\CoursePolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
    }
}
