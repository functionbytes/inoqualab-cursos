<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DiagnosePanelRoutes extends Command
{
    protected $signature = 'diagnose:panel-routes';

    protected $description = 'Genera la lista de URLs reales (con parámetros resueltos) para cada ruta GET del panel manager';

    public function handle(): int
    {
        $routes = collect(Route::getRoutes())
            ->filter(function ($r) {
                $name = $r->getName() ?? '';

                return str_starts_with($name, 'manager.')
                    && in_array('GET', $r->methods())
                    && ! preg_match('/export|destroy|delete|logout|impersonat|secret|download/i', $name);
            })
            ->values();

        $this->info('Total rutas candidatas: '.$routes->count());

        $urls = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            $paramNames = $route->parameterNames();

            $resolvedParams = [];
            $skip = false;

            foreach ($paramNames as $paramName) {
                $value = $this->resolveParam($paramName, $uri);
                if ($value === null) {
                    $skip = true;
                    break;
                }
                $resolvedParams[$paramName] = $value;
            }

            if ($skip) {
                $urls[] = ['name' => $route->getName(), 'uri' => $uri, 'url' => null, 'skipped' => true];

                continue;
            }

            try {
                $url = route($route->getName(), $resolvedParams);
                $urls[] = ['name' => $route->getName(), 'uri' => $uri, 'url' => $url, 'skipped' => false];
            } catch (\Throwable $e) {
                $urls[] = ['name' => $route->getName(), 'uri' => $uri, 'url' => null, 'skipped' => true, 'error' => $e->getMessage()];
            }
        }

        file_put_contents(storage_path('app/route_diagnosis_urls.json'), json_encode($urls, JSON_PRETTY_PRINT));

        $resolved = collect($urls)->where('skipped', false)->count();
        $skipped = collect($urls)->where('skipped', true)->count();
        $this->info("Resueltas: {$resolved} | Omitidas: {$skipped}");
        $this->info('Guardado en storage/app/route_diagnosis_urls.json');

        return 0;
    }

    /** @var array<string, array{0: string, 1: string}|null> */
    private array $paramTableMap = [
        'course' => ['courses', 'slack'],
        'enterprises' => ['enterprises', 'slack'],
        'enterprise' => ['enterprises', 'slack'],
        'distributor' => ['distributors', 'slack'],
        'user' => ['users', 'slack'],
        'category' => ['course_categories', 'slack'],
        'tag' => ['blog_tags', 'slack'],
        'campaign' => ['newsletter_campaigns', 'uid'],
        'contact' => ['contacts', 'slack'],
        'coupon' => ['coupons', 'slack'],
        'bundle' => ['bundles', 'slack'],
        'section' => null,
        'type' => null,
    ];

    private function resolveParam(string $paramName, string $uri): ?string
    {
        static $cache = [];
        $cacheKey = $paramName.'|'.$uri;

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $table = match (true) {
            str_contains($uri, 'enterprises/courses') && $paramName === 'enterprises' => ['enterprises', 'slack'],
            str_contains($uri, 'enterprises/courses') && $paramName === 'course' => ['courses', 'slack'],
            str_contains($uri, 'distributors') && $paramName === 'slack' => ['distributors', 'slack'],
            str_contains($uri, 'users') && $paramName === 'slack' => ['users', 'slack'],
            str_contains($uri, 'courses/reviews') => ['reviews', 'slack'],
            str_contains($uri, 'courses/categories') && $paramName === 'slack' => ['course_categories', 'slack'],
            str_contains($uri, 'courses') && $paramName === 'slack' => ['courses', 'slack'],
            str_contains($uri, 'orders') && $paramName === 'slack' => ['orders', 'slack'],
            str_contains($uri, 'invoices') && $paramName === 'slack' => ['invoices', 'slack'],
            str_contains($uri, 'blogs') && $paramName === 'slack' => ['blogs', 'slack'],
            str_contains($uri, 'certificates') && $paramName === 'slack' => ['inscriptions', 'slack'],
            str_contains($uri, 'results') && $paramName === 'slack' => ['inscriptions', 'slack'],
            str_contains($uri, 'seo') && $paramName === 'slack' => ['seo_redirects', 'id'],
            default => $this->paramTableMap[$paramName] ?? null,
        };

        if ($table === null) {
            return $cache[$cacheKey] = null;
        }

        [$tableName, $column] = $table;

        try {
            $value = DB::table($tableName)->whereNotNull($column)->value($column);
        } catch (\Throwable) {
            $value = null;
        }

        return $cache[$cacheKey] = $value !== null ? (string) $value : null;
    }
}
