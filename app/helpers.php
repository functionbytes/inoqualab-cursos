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

if (! function_exists('devThrottle')) {
    /**
     * Límite de throttle relajado en dominios *.test (Herd/local): el mismo
     * equipo probando/reintentando un flujo (login, quiz, examen...) agota en
     * minutos un límite pensado para tráfico real de producción. En producción
     * (host real) se respeta siempre el límite estricto pasado en $prod.
     */
    function devThrottle(int $prod, ?int $dev = null, int $decayMinutes = 1): string
    {
        $dev ??= $prod * 3;
        $max = str_ends_with(request()->getHost(), '.test') ? $dev : $prod;

        return "throttle:{$max},{$decayMinutes}";
    }
}

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

if (! function_exists('getLogoBase64')) {
    /**
     * Logo como data URI, para contextos que no pueden depender de una URL
     * remota: DomPDF no trae red habilitada por defecto, y el disco de media
     * apunta al dominio de producción en este entorno (ver memoria
     * project_media_disk_points_to_prod) -- un <img src="{{ getlogo() }}">
     * dentro de un PDF generado localmente no cargaría nada. Lee el archivo
     * LOCAL del logo ya configurado (mismo Media Library que usa getlogo())
     * y lo devuelve listo para <img src="...">; null si no hay archivo local
     * (el llamador decide el fallback, normalmente ocultar la imagen).
     */
    function getLogoBase64(): ?string
    {
        $setting = _settingModelCache('page_logo');
        $media = $setting?->getFirstMedia('logo');
        $path = $media?->getPath() ?? public_path('pages/images/logo.png');

        if (! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}

if (! function_exists('setCoupon')) {
    function setCoupon($coupon)
    {
        // secure/httponly/samesite explícitos, igual que XSRF-TOKEN/session (Laravel
        // sí las marca así) -- consistente con el resto de cookies de la app en un
        // sitio HTTPS.
        setcookie('coupon_code', $coupon->code, [
            'expires' => time() + 86400 * 7,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (! function_exists('updateSettings')) {
    function updateSettings($data)
    {
        foreach ($data as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        // Mantiene el caché estático de setting() coherente dentro del mismo
        // request/proceso: sin esto, un patrón leer-vacío→escribir→releer (ej.
        // MantenanceSettingsController::currentSecret()) ve el caché poblado
        // ANTES de esta escritura y sigue creyendo que está vacío, generando
        // un valor nuevo en cada llamada dentro del mismo proceso.
        _settingsCache($data);
    }
}

if (! function_exists('_settingsCache')) {
    function _settingsCache(?array $overrides = null, bool $reset = false): array
    {
        static $cache = null;

        // El caché es estático a nivel de PROCESO PHP: sin este reset explícito,
        // sobrevive entre tests distintos aunque RefreshDatabase reinicie la BD
        // (ver tests/TestCase.php::setUp(), que lo llama antes de cada test).
        // Solo marca sucio (null) y retorna: NO repuebla aquí mismo, porque
        // muchos tests no migran la tabla settings y esta llamada correría
        // antes de que exista, lanzando un QueryException espurio.
        if ($reset) {
            $cache = null;

            return [];
        }

        if ($cache === null) {
            $cache = Setting::query()->select('key', 'value')->pluck('value', 'key')->all();
        }

        if ($overrides !== null) {
            $cache = array_merge($cache, $overrides);
        }

        return $cache;
    }
}

if (! function_exists('forgetSettingsCache')) {
    /**
     * Olvida los cachés de ajustes de este proceso PHP.
     *
     * `_settingsCache()` y `_settingModelCache()` guardan en variables `static`,
     * que viven lo que viva el proceso. En web da igual (un proceso por
     * petición), pero un worker de cola arrancado con `--max-jobs=500` procesa
     * cientos de trabajos con el mismo proceso: si el manager cambia un ajuste
     * desde el panel, el worker sigue viendo el valor viejo hasta que se
     * reinicia. Se llama desde `Queue::before()` en AppServiceProvider, de modo
     * que cada trabajo arranca leyendo lo que hay de verdad en la base.
     */
    function forgetSettingsCache(): void
    {
        _settingsCache(null, reset: true);
        _settingModelCache(reset: true);
    }
}

if (! function_exists('setting')) {
    function setting($key, $default = '')
    {
        // Devuelve $default cuando la clave no existe o su valor es vacío/null:
        // ~54 llamadas pasaban un default que antes se descartaba en silencio.
        // El default '' preserva el comportamiento previo cuando no se pasa uno.
        $value = _settingsCache()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return is_numeric($value) ? $value + 0 : $value;
    }
}

if (! function_exists('settingEnabled')) {
    /**
     * Lee un ajuste de tipo interruptor y devuelve un booleano de verdad.
     *
     * Existe porque `setting()` normaliza los valores numéricos con
     * `$value + 0`, así que un ajuste guardado como '1' vuelve como **int** 1 y
     * uno guardado como '0' como int 0. Toda comparación estricta contra la
     * cadena ('=== \'1\'', '!== \'0\'') es por tanto siempre falsa o siempre
     * verdadera, y el interruptor del panel deja de tener efecto sin que nada
     * falle. Pasó en tres sitios a la vez: desactivar el boletín no lo
     * desactivaba, su casilla salía marcada igualmente, e IndexNow no se
     * activaba nunca.
     *
     * @param  bool  $default  Valor cuando el ajuste no existe o está vacío.
     */
    function settingEnabled(string $key, bool $default = false): bool
    {
        $value = setting($key, null);

        if ($value === null || $value === '') {
            return $default;
        }

        // FILTER_VALIDATE_BOOLEAN entiende 1/0, '1'/'0', 'true'/'false',
        // 'yes'/'no' y 'on'/'off'; devuelve null ante cualquier otra cosa, y
        // entonces mandar el default es más seguro que adivinar.
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }
}

if (! function_exists('portalVariant')) {
    /**
     * Sufijo de la vista del portal del alumno para el ajuste dado.
     *
     * El manager elige entre dos diseños por pantalla en Configuración › Portal
     * del alumno; el valor se concatena al nombre de la vista, así que aquí solo
     * se distingue 'b' del resto: una fila corrupta en `settings` cae en la
     * variante por defecto en vez de tumbar el portal buscando una vista que no
     * existe.
     */
    function portalVariant(string $key): string
    {
        return setting($key, 'a') === 'b' ? '-b' : '';
    }
}

if (! function_exists('removeCoupon')) {
    function removeCoupon()
    {
        if (isset($_COOKIE['coupon_code'])) {
            // Mismos atributos (path/secure/samesite) con los que se creó en
            // setCoupon(): sin ellos el navegador la trata como una cookie distinta
            // y la original sobrevive — el cupón "quitado" se seguía consumiendo.
            setcookie('coupon_code', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            unset($_COOKIE['coupon_code']);
        }
    }
}

if (! function_exists('getCoupon')) {
    function getCoupon()
    {
        // Solo la cookie que setea checkCouponValidityForCart(): aceptar también
        // un header arbitrario permitía evaluar cupones vía GET /checkout (sin
        // throttle) esquivando el rate-limit de /checkout/coupon/apply, y ningún
        // JS del sitio envía ese header.
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

        // Filtro available: un curso/paquete retirado tras agregarlo al carrito no
        // debe cobrarse ni venderse (queda fuera de la colección → se salta).
        $courses = $courseSlacks
            ? Course::whereIn('slack', $courseSlacks)->where('available', 1)->get()->keyBy('slack')
            : collect();
        $bundles = $bundleSlacks
            ? Bundle::whereIn('slack', $bundleSlacks)->where('available', 1)->get()->keyBy('slack')
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

            // Un curso se matricula una sola vez por usuario: qty>1 cobraría N veces
            // pero solo genera una inscripción. Se fuerza a 1 (los paquetes sí permiten qty).
            $qty = $type === 'course' ? 1 : max(1, (int) ($ci['qty'] ?? 1));
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
            // getSetting() devuelve el modelo Setting completo (sin parámetros);
            // "truncate_price"/"no_of_decimals" son claves individuales en la
            // tabla settings (key/value), no atributos de ese modelo.
            $noOfDecimals = (int) (Setting::where('key', 'no_of_decimals')->value('value') ?? 2);

            if ($truncate) {
                $truncatePrice = (int) (Setting::where('key', 'truncate_price')->value('value') ?? 0);

                if ($truncatePrice === 1 || $forceTruncate === true) {
                    if ($price < 1000000) {
                        $price = number_format($price, $noOfDecimals);
                    } elseif ($price < 1000000000) {
                        $price = number_format($price / 1000000, $noOfDecimals).'M';
                    } else {
                        $price = number_format($price / 1000000000, $noOfDecimals).'B';
                    }
                }
            } elseif ($noOfDecimals > 0) {
                $price = number_format($price, $noOfDecimals);
            } else {
                $price = number_format($price, $noOfDecimals, '.', ',');
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
    function _settingModelCache(?string $key = null, bool $reset = false): ?Setting
    {
        static $cache = [];

        if ($reset) {
            $cache = [];

            return null;
        }

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

if (! function_exists('revokeUserSessions')) {
    /**
     * Cierra de verdad la sesión abierta de un usuario, en el driver que sea.
     *
     * El proyecto guarda en `users.session` el id de la sesión con la que el
     * usuario entró (lo usa CheckSession para el "último login gana"), y
     * destruirla hay que pedírselo al handler de sesión activo. El código que
     * había llamaba a `$user->sessions()->delete()`, que borra filas de la
     * tabla `sessions`; pero SESSION_DRIVER es `file` desde hace tiempo, así que
     * esa tabla solo guarda fósiles (sus filas son de octubre de 2025) y el
     * borrado no cerraba nada. Restablecer la contraseña dejaba dentro a quien
     * ya tuviera la sesión abierta.
     *
     * Se deja `users.session` a null, coherente con lo que hace el login.
     * No se guarda el modelo: lo hace quien llama, junto al resto de cambios.
     */
    function revokeUserSessions(User $user): void
    {
        if ($user->session) {
            // El handler puede fallar si el fichero ya no está; no debe impedir
            // el cambio de contraseña, que es lo importante de la operación.
            try {
                Session::getHandler()->destroy($user->session);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $user->session = null;
    }
}

if (! function_exists('paginationNumber')) {
    /**
     * Tamaño de página para los listados del panel. Respeta ?per_page=X de
     * la request (selector "items por página" de managers.includes.pagination-footer)
     * cuando el valor está en la whitelist; si no, cae al $value explícito
     * del caller (para las vistas que ya pedían un tamaño propio, ej.
     * ->paginate(paginationNumber(20))) o al default de config.
     */
    function paginationNumber($value = null)
    {
        $allowed = [10, 20, 50, 100, 200];
        $requested = request()->input('per_page');

        if ($requested !== null && in_array((int) $requested, $allowed, true)) {
            return (int) $requested;
        }

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
    // Carbon::format() usa el formateador nativo de PHP, que ignora el locale
    // de la app (config('app.locale') = 'es') -- salía "September 15, 2026" en
    // correos en español. translatedFormat() sí respeta el locale indicado.
    // Sin ucwords(): en español "de" no se capitaliza ("15 de septiembre de 2026").
    return Carbon::parse($dates)->locale('es')->translatedFormat('j \d\e F \d\e Y');
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

if (! function_exists('parse_date_range')) {
    /**
     * Trocea el rango "dd/mm/YYYY - dd/mm/YYYY" que emiten los daterangepicker
     * del panel y devuelve [inicio, fin] normalizados al día completo.
     *
     * Los informes hacían `explode(' - ', $request->range)` y accedían a
     * `$date[1]` a pelo: entrar a la URL de generación sin el parámetro (o con
     * un valor suelto) reventaba con "Undefined array key 1" y un 500. Aquí se
     * devuelve null y el llamador decide qué responder.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    function parse_date_range(?string $range): ?array
    {
        $parts = array_map('trim', explode(' - ', (string) $range));

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return null;
        }

        try {
            $start = Carbon::parse($parts[0])->startOfDay();
            $end = Carbon::parse($parts[1])->endOfDay();
        } catch (Throwable) {
            return null;
        }

        return $start->lessThanOrEqualTo($end) ? [$start, $end] : [$end->startOfDay(), $start->endOfDay()];
    }
}
