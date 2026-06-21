<?php

use App\Models\Bundle\Bundle;
use App\Models\Coupon\Coupon;
use App\Models\Coupon\CouponUsage;
use App\Models\Course\Course;
use App\Models\Setting\Setting;
use App\Models\User;
use App\Services\SchemaOrgService;
use App\Services\SeoService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

if (! function_exists('getlogo')) {
    function getlogo()
    {
        $setting = _settingModelCache('page_logo');
        if (! $setting) {
            return asset('/pages/images/logo.png');
        }

        return count($setting->getMedia('logo')) > 0
            ? $setting->getfirstMedia('logo')->getfullUrl()
            : asset('/pages/images/logo.png');
    }
}

if (! function_exists('setCoupon')) {
    function setCoupon($coupon)
    {
        $theTime = time() + 86400 * 7;
        setcookie('coupon_code', $coupon->code, $theTime, '/');
    }
}

if (! function_exists('updateSettings')) {
    function updateSettings($data)
    {
        foreach ($data as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }
    }
}

if (! function_exists('_settingsCache')) {
    function _settingsCache(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = Setting::query()->select('key', 'value')->pluck('value', 'key')->all();
        }

        return $cache;
    }
}

if (! function_exists('setting')) {
    function setting($key)
    {
        $value = _settingsCache()[$key] ?? '';

        return is_numeric($value) ? $value + 0 : $value;
    }
}

if (! function_exists('removeCoupon')) {
    function removeCoupon()
    {
        if (isset($_COOKIE['coupon_code'])) {
            setcookie('coupon_code', '', time() - 3600);
            unset($_COOKIE['coupon_code']);
        }
    }
}

if (! function_exists('getCoupon')) {
    function getCoupon()
    {
        if (request()->hasHeader('coupon_code')) {
            return request()->header('coupon_code');
        }

        if (isset($_COOKIE['coupon_code'])) {
            return $_COOKIE['coupon_code'];
        }

        return '';
    }
}

if (! function_exists('getCouponDiscount')) {
    function getCouponDiscount($subtotal, $code = '')
    {
        $amount = 0;
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return $amount;
        }

        $date = date('Y-m-d');

        // Fechas NULL = sin restricción de vigencia (coherente con el flujo multi-item).
        $vigente = (! $coupon->start_date || $coupon->start_date <= $date)
            && (! $coupon->end_date || $coupon->end_date >= $date);

        if ($vigente) {
            // Porcentaje clampeado a [0,100]; descuento nunca supera el subtotal.
            $amount = $coupon->type == 0
                ? min((float) $coupon->amount, (float) $subtotal)
                : round((float) $subtotal * min(100, max(0, (float) $coupon->amount)) / 100, 2);
        } else {
            removeCoupon();
        }

        return $amount;
    }
}

if (! function_exists('cartUnits')) {
    /**
     * Total de UNIDADES en el carrito (suma de qty), no de líneas.
     * Semántica única del badge del carrito en header, drawer y respuestas JSON.
     */
    function cartUnits(): int
    {
        $total = 0;
        foreach (session('cart', []) as $item) {
            $total += max(1, (int) ($item['qty'] ?? 1));
        }

        return $total;
    }
}

if (! function_exists('cartCheckoutLines')) {
    /**
     * Resuelve las líneas del carrito de sesión a precios autoritativos desde BD.
     * Devuelve [array $lines, float $subtotal]. Cada línea:
     * ['type','id','slack','title','qty','unit','amount'].
     */
    function cartCheckoutLines(): array
    {
        $cart = session('cart', []);
        $lines = [];
        $subtotal = 0;

        // Resolver en lote (evita N+1): una consulta por tipo.
        $courseSlacks = [];
        $bundleSlacks = [];
        foreach ($cart as $ci) {
            if (($ci['type'] ?? null) === 'course') {
                $courseSlacks[] = $ci['slack'] ?? '';
            } elseif (($ci['type'] ?? null) === 'bundle') {
                $bundleSlacks[] = $ci['slack'] ?? '';
            }
        }

        $courses = $courseSlacks
            ? Course::whereIn('slack', $courseSlacks)->get()->keyBy('slack')
            : collect();
        $bundles = $bundleSlacks
            ? Bundle::whereIn('slack', $bundleSlacks)->get()->keyBy('slack')
            : collect();

        foreach ($cart as $ci) {
            $type = $ci['type'] ?? null;
            $slack = $ci['slack'] ?? '';

            $compare = null;
            if ($type === 'course') {
                $model = $courses->get($slack);
                if (! $model) {
                    continue;
                }
                $onSale = $model->payment != 0 && $model->promotion == 1 && $model->discount < $model->price;
                $unit = $model->payment == 0 ? 0 : ($onSale ? $model->discount : $model->price);
                $compare = $onSale ? (float) $model->price : null;
            } elseif ($type === 'bundle') {
                $model = $bundles->get($slack);
                if (! $model) {
                    continue;
                }
                $unit = $model->price;
            } else {
                continue;
            }

            $qty = max(1, (int) ($ci['qty'] ?? 1));
            $amount = (float) $unit * $qty;
            $subtotal += $amount;

            $lines[] = [
                'type' => $type,
                'id' => $model->id,
                'slack' => $slack,
                'title' => $model->title,
                'qty' => $qty,
                'unit' => (float) $unit,
                'amount' => $amount,
                'compare' => $compare,
                'compare_amount' => $compare !== null ? $compare * $qty : null,
            ];
        }

        return [$lines, $subtotal];
    }
}

if (! function_exists('cartCouponDiscount')) {
    /**
     * Calcula el descuento de un cupón aplicado SOLO a las líneas elegibles del carrito.
     * Si el cupón no tiene restricción de cursos/paquetes, aplica a todas las líneas.
     */
    function cartCouponDiscount(array $lines, $coupon): float
    {
        $courseIds = ! empty($coupon->course_ids) ? explode(',', $coupon->course_ids) : [];
        $bundleIds = ! empty($coupon->bundle_ids) ? explode(',', $coupon->bundle_ids) : [];
        $restricted = ! empty($courseIds) || ! empty($bundleIds);

        $eligible = 0;
        foreach ($lines as $line) {
            if (! $restricted) {
                $eligible += $line['amount'];
            } elseif ($line['type'] === 'course' && in_array((string) $line['id'], $courseIds)) {
                $eligible += $line['amount'];
            } elseif ($line['type'] === 'bundle' && in_array((string) $line['id'], $bundleIds)) {
                $eligible += $line['amount'];
            }
        }

        if ($eligible <= 0) {
            return 0;
        }

        // type 1 = porcentaje, type 0 = monto fijo
        $discount = $coupon->type == 1
            ? round($eligible * min(100, max(0, (float) $coupon->amount)) / 100, 2)
            : (float) $coupon->amount;

        // El descuento nunca puede superar el monto elegible (evita totales en cero/negativos).
        return min($discount, $eligible);
    }
}

if (! function_exists('checkCouponValidityForCart')) {
    /**
     * Valida un cupón contra el carrito completo (multi-item) y devuelve el resultado
     * con el descuento aplicado solo a las líneas elegibles.
     */
    function checkCouponValidityForCart($code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'El cupón no es válido'];
        }

        if (! $coupon->available) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'El cupón no está disponible'];
        }

        [$lines, $subtotal] = cartCheckoutLines();

        if (empty($lines)) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'Tu carrito está vacío'];
        }

        $date = date('Y-m-d');

        if ($coupon->limit > 0) {
            $totalCouponUsage = CouponUsage::where('coupon_id', $coupon->id)->sum('usage_count');
            if ($totalCouponUsage >= $coupon->limit) {
                removeCoupon();

                return ['success' => false, 'status' => false, 'message' => 'Se ha alcanzado el límite de uso total del cupón.'];
            }
        }

        if (auth()->check()) {
            $couponUsageByUser = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', auth()->id())
                ->first();

            if ($couponUsageByUser && $couponUsageByUser->usage_count >= 1) {
                removeCoupon();

                return ['success' => false, 'status' => false, 'message' => 'Ya has utilizado este cupón.'];
            }
        }

        // Fechas NULL = sin restricción de vigencia (coherente con couponUsableNow del consumo).
        if (($coupon->start_date && $coupon->start_date > $date) || ($coupon->end_date && $coupon->end_date < $date)) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'El cupón ha caducado'];
        }

        if ($subtotal < (float) $coupon->min_price) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'No se ha alcanzado el importe mínimo del pedido para utilizar este cupón'];
        }

        $discount = cartCouponDiscount($lines, $coupon);

        if ($discount <= 0) {
            removeCoupon();

            return ['success' => false, 'status' => false, 'message' => 'El cupón no es válido para los artículos de tu carrito.'];
        }

        $total = max(0, $subtotal - $discount);
        setCoupon($coupon);

        return [
            'success' => true,
            'status' => true,
            'discount' => $discount,
            'total' => $total,
            'subtotal' => $subtotal,
            'code' => $code,
            'message' => 'Cupón aplicado exitosamente',
        ];
    }
}

if (! function_exists('formatPrice')) {
    function formatPrice($price, $truncate = false, $forceTruncate = false, $addSymbol = true, $numberFormat = true)
    {
        if (request()->hasHeader('Currency-Code')) {
            $price = floatval($price) / (floatval(config('settings.currency_rate', 1)) ?: 1);
            $price = floatval($price) * floatval(ApiCurrencyMiddleWare::currencyData()->rate);
        } elseif (Session::has('currency_code') && Session::has('local_currency_rate')) {
            $price = floatval($price) / (floatval(config('settings.currency_rate', 1)) ?: 1);
            $price = floatval($price) * floatval(Session::get('local_currency_rate'));
        }

        if ($numberFormat) {
            if ($truncate) {
                if (getSetting('truncate_price') == 1 || $forceTruncate === true) {
                    if ($price < 1000000) {
                        $price = number_format($price, getSetting('no_of_decimals'));
                    } elseif ($price < 1000000000) {
                        $price = number_format($price / 1000000, getSetting('no_of_decimals')).'M';
                    } else {
                        $price = number_format($price / 1000000000, getSetting('no_of_decimals')).'B';
                    }
                }
            } elseif (getSetting('no_of_decimals') > 0) {
                $price = number_format($price, getSetting('no_of_decimals'));
            } else {
                $price = number_format($price, getSetting('no_of_decimals'), '.', ',');
            }
        }

        if (! $addSymbol) {
            return $price;
        }

        if (request()->hasHeader('Currency-Code')) {
            $symbol = ApiCurrencyMiddleWare::currencyData()->symbol;
            $symbolAlignment = ApiCurrencyMiddleWare::currencyData()->alignment;
        } else {
            $symbol = Session::has('currency_symbol') ? Session::get('currency_symbol') : config('settings.currency_symbol', '$');
            $symbolAlignment = Session::has('currency_symbol_alignment') ? Session::get('currency_symbol_alignment') : config('settings.currency_symbol_alignment', 0);
        }

        return match ((int) $symbolAlignment) {
            0 => $symbol.$price,
            1 => $price.$symbol,
            2 => $symbol.' '.$price,
            default => $price.' '.$symbol,
        };
    }
}

if (! function_exists('getSubTotal')) {
    function getSubTotal($price, $couponDiscount = true, $couponCode = '', $addTax = true)
    {
        $amount = $couponDiscount ? getCouponDiscount($price, $couponCode) : 0;

        return $price - $amount;
    }
}

if (! function_exists('_settingModelCache')) {
    function _settingModelCache(string $key): ?Setting
    {
        static $cache = [];
        if (! array_key_exists($key, $cache)) {
            $cache[$key] = Setting::where('key', '=', $key)->first();
        }

        return $cache[$key];
    }
}

if (! function_exists('getFavicon')) {
    function getFavicon()
    {
        $setting = _settingModelCache('page_favicon');
        if (! $setting) {
            return asset('/pages/images/favicon.png');
        }

        return count($setting->getMedia('favicon')) > 0
            ? $setting->getfirstMedia('favicon')->getfullUrl()
            : asset('/pages/images/favicon.png');
    }
}

if (! function_exists('getMeta')) {
    function getMeta()
    {
        $setting = _settingModelCache('meta_image');
        if (! $setting) {
            return asset('/pages/images/favicon.png');
        }

        return count($setting->getMedia('meta')) > 0
            ? $setting->getfirstMedia('meta')->getfullUrl()
            : asset('/pages/images/favicon.png');
    }
}

if (! function_exists('getUrl')) {
    function getUrl()
    {
        return URL::to('/');
    }
}

if (! function_exists('getLogo')) {
    function getLogo()
    {
        return getlogo();
    }
}

if (! function_exists('getSetting')) {
    function getSetting()
    {
        static $cache = null;

        return $cache ??= Setting::first();
    }
}

if (! function_exists('clearSessionExceptCurrent')) {
    function clearSessionExceptCurrent(User $user)
    {
        $user->sessions()->where('id', '<>', session()->getId())->delete();
    }
}

if (! function_exists('paginationNumber')) {
    function paginationNumber($value = null)
    {
        return $value ?? config('settings.pagination', 15);
    }
}

function certificate_date($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('d-m-Y'));
}

function humanize_date($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('F j, Y'));
}

function month($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('Y'));
}

function day($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('j'));
}

function year($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('Y'));
}

function dates($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('d-m-Y'));
}

function input_date($dates): string
{
    $date = Carbon::parse($dates);

    return ucwords($date->format('d-m-Y'));
}

// ─── SEO Helpers ────────────────────────────────────────────────────────────

if (! function_exists('seo')) {
    function seo(): SeoService
    {
        return app(SeoService::class);
    }
}

if (! function_exists('seo_title')) {
    function seo_title(?string $title = null, bool $suffix = true): string|SeoService
    {
        if ($title === null) {
            return app(SeoService::class)->render();
        }

        return app(SeoService::class)->setTitle($title, $suffix);
    }
}

if (! function_exists('seo_description')) {
    function seo_description(?string $desc = null): SeoService
    {
        return app(SeoService::class)->setDescription($desc ?? '');
    }
}

if (! function_exists('seo_image')) {
    function seo_image(string $url): SeoService
    {
        return app(SeoService::class)->setOgImage($url);
    }
}

if (! function_exists('seo_canonical')) {
    function seo_canonical(string $url): SeoService
    {
        return app(SeoService::class)->setCanonical($url);
    }
}

if (! function_exists('seo_render')) {
    function seo_render(): string
    {
        return app(SeoService::class)->render();
    }
}

if (! function_exists('seo_from_model')) {
    function seo_from_model(Model $model): SeoService
    {
        return app(SeoService::class)->loadFromModel($model);
    }
}

if (! function_exists('schema_org')) {
    function schema_org(): SchemaOrgService
    {
        return app(SchemaOrgService::class);
    }
}
