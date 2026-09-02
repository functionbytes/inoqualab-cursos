<?php

namespace App\Providers;

use App\Html\FormBuilder;
use App\Html\HtmlBuilder;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\Course\CourseCategorie;
use App\Models\Faq\Faq;
use App\Models\Inscription;
use App\Models\Invoice\Invoice;
use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use App\Models\Mailer\MailerVariable;
use App\Models\Order\Order;
use App\Models\Setting\Setting;
use App\Models\Testimonie;
use App\Models\User;
use App\Models\Users\Certificate;
use App\Observers\Mailer\MailerLayoutObserver;
use App\Observers\Mailer\MailerTemplateObserver;
use App\Observers\Mailer\MailerVariableObserver;
use App\Observers\User\UserRoleObserver;
use App\Policies\CertificatePolicy;
use App\Policies\CoursePolicy;
use App\Policies\InscriptionPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\UserPolicy;
use App\Services\SchemaOrgService;
use App\Services\SeoService;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        return static::$settingCache ??= Setting::first() ?? new Setting;
    }

    public function setting(): ?object
    {
        return static::$settingCache ??= Setting::first() ?? new Setting;
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

        // Política única de contraseñas para todo el proyecto.
        //
        // Antes cada Form Request traía su propio mínimo y quedaban al revés de
        // lo razonable: los perfiles de staff con acceso a panel (support,
        // accounting, distributor, enterprise) pedían min:6 mientras el cliente
        // final pedía min:8 -- las cuentas con más privilegio eran las de
        // contraseña más débil.
        //
        // uncompromised() consulta el rango k-anónimo de HaveIBeenPwned, así que
        // se deja fuera en testing: metería una llamada de red en cada test que
        // valide una contraseña y volvería la suite dependiente de un servicio
        // externo. Si la API no responde en producción, la regla deja pasar.
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->runningUnitTests() ? $rule : $rule->uncompromised();
        });

        Blade::directive('seoTags', fn () => '<?php echo app(\App\Services\SeoService::class)->render(); ?>');
        Blade::directive('schemaOrg', fn () => '<?php if ($schema = app(\App\Services\SchemaOrgService::class)->organization()): echo \'<script type="application/ld+json">\'.json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).\'</script>\'; endif; ?>');

        // Los cachés de ajustes viven en variables `static`, es decir, mientras
        // viva el proceso PHP. Un worker arrancado con --max-jobs=500 procesa
        // cientos de trabajos sin reiniciarse, así que un ajuste cambiado desde
        // el panel no llegaba a los trabajos ya encolados: seguían usando la
        // foto que el worker leyó al arrancar. Olvidarlos aquí hace que cada
        // trabajo empiece con el estado real de la base.
        Queue::before(function (JobProcessing $event) {
            forgetSettingsCache();
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

        // superadmin autoriza siempre, incluso si un permiso nuevo se crea en
        // código antes de re-sembrar `RolesAndPermissionsSeeder` (sin esto,
        // superadmin dependería de tener el permiso ya sincronizado en BD).
        Gate::before(fn (User $user) => $user->role === 'superadmin' ? true : null);

        $this->registerPolicies();
        $this->registerCatalogCacheInvalidation();
    }

    /**
     * Invalida la caché del catálogo público (home) cuando cambia un curso,
     * paquete, categoría, FAQ o testimonio. Ver Pages\PagesController::index
     * (Cache::remember).
     */
    private function registerCatalogCacheInvalidation(): void
    {
        $forget = fn () => Cache::forget('catalog.home');

        foreach ([Course::class, Bundle::class, CourseCategorie::class, Faq::class, Testimonie::class] as $model) {
            $model::saved($forget);
            $model::deleted($forget);
        }
    }

    /**
     * Registro explícito de Policies (los modelos viven en sub-namespaces,
     * por lo que el auto-discovery convencional no los resuelve).
     */
    private function registerPolicies(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Inscription::class, InscriptionPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
