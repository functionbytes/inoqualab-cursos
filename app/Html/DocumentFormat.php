<?php

namespace App\Html;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Formato compartido de las vistas de orden y factura (propuestas A/B/C):
 * dinero en COP con separador de miles colombiano, fechas en español y el
 * "tono" del estado (condition_id 1 Generada, 2 Pendiente, 3 Rechazada,
 * 4 Pagada — mismos ids en órdenes y facturas).
 */
class DocumentFormat
{
    /** Diseños elegibles para orden/factura: original = la vista anterior. */
    public const DESIGNS = [
        'original' => 'Actual',
        'a' => 'Comprobante',
        'b' => 'Mesa de trabajo',
        'c' => 'Ficha técnica',
    ];

    /**
     * Diseño a mostrar: ?diseno= (previsualizar sin cambiar el ajuste) o el
     * elegido en Configuración de facturación. null = la vista original.
     */
    public static function design(): ?string
    {
        $requested = request('diseno');
        $design = array_key_exists((string) $requested, self::DESIGNS)
            ? $requested
            : setting('panel_documents_design', 'a');

        return in_array($design, ['a', 'b', 'c'], true) ? $design : null;
    }

    /**
     * Mantiene el ?diseno= de la previsualización en los enlaces entre
     * factura y reparto; sin previsualización devuelve la url tal cual.
     */
    public static function link(string $url): string
    {
        $requested = request('diseno');

        return array_key_exists((string) $requested, self::DESIGNS) ? $url.'?diseno='.$requested : $url;
    }

    public static function money(mixed $value): string
    {
        return '$ '.number_format((float) $value, 0, ',', '.');
    }

    public static function percent(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals, ',', '.').' %';
    }

    public static function date(mixed $value, string $format = 'j \d\e F \d\e Y'): ?string
    {
        return $value ? Carbon::parse($value)->locale('es')->translatedFormat($format) : null;
    }

    /**
     * @return 'paid'|'rejected'|'open'
     */
    public static function tone(?int $conditionId): string
    {
        return match ($conditionId) {
            4 => 'paid',
            3 => 'rejected',
            default => 'open',
        };
    }

    /**
     * Convierte el arreglo de InvoicesController::details()
     * ([empresa => [[course, quantity, amount, totalAmount], ..., 'totalEnterprise' => x]])
     * en grupos ordenados de mayor a menor valor.
     *
     * @return Collection<int, array{name: string, total: float, seats: float, courses: Collection}>
     */
    public static function enterpriseGroups(array $details): Collection
    {
        return collect($details)
            ->map(function ($rows, $name) {
                $courses = collect($rows)->filter(fn ($row) => is_array($row))->sortByDesc('totalAmount')->values();

                return [
                    'name' => (string) $name,
                    'total' => (float) ($rows['totalEnterprise'] ?? 0),
                    'seats' => (float) $courses->sum('quantity'),
                    'courses' => $courses,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }
}
