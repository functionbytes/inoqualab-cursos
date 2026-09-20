<?php

namespace App\Services\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Lógica común de navegación "icon rail + panel lateral" compartida por
 * cada portal (managers, supports, distributors, enterprises, accountings).
 * Cada portal solo define su propio menu() con los mini-items/sidebars;
 * el resto (detección de item/sidebar activo, filtrado por permisos,
 * colapso a link directo cuando solo hay un item visible) es idéntico.
 *
 * Ver App\Services\ManagerNavService para la implementación de referencia.
 */
abstract class BaseNavService
{
    /**
     * Estructura completa del menú: mini-items (rail de iconos) + sidebars
     * (paneles laterales con secciones e items), propia de cada portal.
     */
    abstract protected static function menu(): array;

    public static function getMiniItemsForUser(): Collection
    {
        return collect(static::menu()['miniItems'])->sortBy('order')->values();
    }

    public static function getAllSidebars(): array
    {
        return static::menu()['sidebars'];
    }

    /**
     * Verificar si un usuario puede acceder a un item del menú: respeta
     * tanto el permiso Spatie (`can`, con soporte para "a|b") como el
     * feature-flag de `setting('module_x')`.
     */
    public static function userCanAccessItem(array $item, User $user): bool
    {
        if (! empty($item['setting']) && setting($item['setting']) === 0) {
            return false;
        }

        if (empty($item['permission'])) {
            return true;
        }

        $permissions = array_map('trim', explode('|', $item['permission']));

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Datos completos de navegación para el template: miniItems, sidebars,
     * y el estado activo (qué panel lateral abrir según la ruta actual).
     *
     * @return array{miniItems: Collection, sidebars: array, activeSidebarId: ?string, activeMiniId: ?string, activeItemRoute: ?string}
     */
    public static function getNavDataForUser(): array
    {
        $user = auth()->user();
        $miniItems = static::getMiniItemsForUser();
        $sidebars = static::getAllSidebars();

        // Si para este usuario un sidebar termina con un solo item visible
        // (por permisos/settings), no tiene sentido abrir un panel lateral
        // con una única fila: el icono del rail se vuelve un link directo,
        // igual que Dashboard.
        if ($user) {
            $miniItems = $miniItems->map(function ($miniItem) use ($sidebars, $user) {
                if (! empty($miniItem['url']) || ! isset($sidebars[$miniItem['sidebar_id']])) {
                    return $miniItem;
                }

                $visibleItems = collect($sidebars[$miniItem['sidebar_id']]['sections'])
                    ->flatMap(fn ($section) => $section['items'])
                    ->filter(fn ($item) => static::userCanAccessItem($item, $user));

                if ($visibleItems->count() === 1) {
                    $miniItem['url'] = $visibleItems->first()['route'];
                }

                return $miniItem;
            });
        }

        $currentRoute = request()->route()?->getName();

        // Coincidencia directa: un mini-item con URL propia (ej. Dashboard).
        $directMatch = $currentRoute
            ? $miniItems->first(fn ($item) => ! empty($item['url']) && $item['url'] === $currentRoute)
            : null;

        if ($directMatch) {
            $directSidebarId = $directMatch['sidebar_id'];

            return [
                'miniItems' => $miniItems,
                'sidebars' => $sidebars,
                'activeSidebarId' => isset($sidebars[$directSidebarId]) ? $directSidebarId : null,
                'activeMiniId' => $directSidebarId,
                'activeItemRoute' => $currentRoute,
            ];
        }

        $candidateSidebarId = static::findActiveSidebar($sidebars, $user)
            ?? static::findSidebarByRoutePrefix($sidebars);

        $activeItemRoute = $candidateSidebarId
            ? static::findBestMatchingItemRoute($sidebars[$candidateSidebarId] ?? [])
            : null;

        $activeSidebarId = $activeItemRoute ? $candidateSidebarId : null;

        return [
            'miniItems' => $miniItems,
            'sidebars' => $sidebars,
            'activeSidebarId' => $activeSidebarId,
            'activeMiniId' => $candidateSidebarId,
            'activeItemRoute' => $activeItemRoute,
        ];
    }

    private static function findActiveSidebar(array $sidebars, ?User $user = null): ?string
    {
        if (! $user) {
            return null;
        }

        foreach ($sidebars as $sidebarId => $sidebar) {
            foreach ($sidebar['sections'] as $section) {
                foreach (static::flattenItems($section['items']) as $item) {
                    if (! empty($item['route']) && static::userCanAccessItem($item, $user) && request()->routeIs($item['route'].'*')) {
                        return $sidebarId;
                    }
                }
            }
        }

        return null;
    }

    private static function findSidebarByRoutePrefix(array $sidebars): ?string
    {
        $currentRoute = request()->route()?->getName();

        if (! $currentRoute) {
            return null;
        }

        $prefix = explode('.', $currentRoute)[0];

        foreach ($sidebars as $sidebarId => $sidebar) {
            foreach ($sidebar['sections'] as $section) {
                foreach (static::flattenItems($section['items']) as $item) {
                    if (! empty($item['route']) && str_starts_with($item['route'], $prefix.'.')) {
                        return $sidebarId;
                    }
                }
            }
        }

        return null;
    }

    private static function findBestMatchingItemRoute(array $sidebar): ?string
    {
        $currentRoute = request()->route()?->getName();

        if (! $currentRoute) {
            return null;
        }

        $currentParts = explode('.', $currentRoute);
        $currentDepth = count($currentParts);
        $bestRoute = null;
        $bestMatchDepth = 0;

        $items = collect($sidebar['sections'] ?? [])->flatMap(fn ($s) => static::flattenItems($s['items'] ?? []))->all();

        foreach ($items as $item) {
            $itemRoute = $item['route'] ?? '';

            if (! $itemRoute) {
                continue;
            }

            if (request()->routeIs($itemRoute.'*')) {
                return $itemRoute;
            }

            $itemParts = explode('.', $itemRoute);
            $itemDepth = count($itemParts);

            $matching = 0;
            foreach ($itemParts as $i => $seg) {
                if (($currentParts[$i] ?? null) === $seg) {
                    $matching++;
                } else {
                    break;
                }
            }

            $isAncestor = $matching === $itemDepth && $currentDepth > $itemDepth;
            $isSibling = $currentDepth > 1 && $itemDepth === $currentDepth && $matching === $itemDepth - 1;

            if (! $isAncestor && ! $isSibling) {
                continue;
            }

            if ($matching > $bestMatchDepth) {
                $bestMatchDepth = $matching;
                $bestRoute = $itemRoute;
            }
        }

        return $bestRoute;
    }

    /**
     * Aplana items con hijos para las búsquedas de ruta activa: incluye
     * tanto el item padre (si tiene ruta propia) como sus children.
     */
    private static function flattenItems(array $items): array
    {
        $flat = [];

        foreach ($items as $item) {
            if (! empty($item['route'])) {
                $flat[] = $item;
            }

            foreach ($item['children'] ?? [] as $child) {
                $flat[] = $child;
            }
        }

        return $flat;
    }
}
