<?php

namespace App\Services;

use App\Models\Seo\SeoAuditLog;
use App\Models\Seo\SeoMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SeoAuditService
{
    public function auditUrl(string $url): array
    {
        try {
            $this->validatePublicUrl($url);

            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SeoAuditBot/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                return $this->errorResult("La URL retornó HTTP {$response->status()}");
            }

            $body = $response->body();
            $checks = $this->runChecks($body, $url);

            if (strlen($body) > 200) {
                $readability = $this->checkReadability($body);
                if ($readability['score'] < 30) {
                    $checks[] = $this->issue('error', 'readability_very_hard', "Contenido muy difícil de leer (score: {$readability['score']}/100)", 'Simplifica el contenido para mejorar la legibilidad', 5);
                } elseif ($readability['score'] < 50) {
                    $checks[] = $this->issue('warning', 'readability_hard', "Contenido difícil de leer (score: {$readability['score']}/100)", 'Reduce la longitud de las oraciones y usa vocabulario más simple', 3);
                }
            }

            return $this->buildResult($url, $checks);
        } catch (\Exception $e) {
            return $this->errorResult('No se pudo acceder a la URL: '.$e->getMessage());
        }
    }

    public function auditMeta(SeoMeta $meta): array
    {
        $result = $this->buildResult(null, $this->runMetaChecks($meta));

        $meta->updateQuietly([
            'seo_score' => $result['score'],
            'seo_grade' => $result['grade'],
            'seo_audited_at' => now(),
        ]);

        SeoAuditLog::create([
            'seo_meta_id' => $meta->id,
            'score' => $result['score'],
            'grade' => $result['grade'],
            'issues_count' => count($result['issues']),
            'issues' => $result['issues'],
            'passed_count' => count($result['passed']),
            'audited_at' => now(),
        ]);

        return $result;
    }

    public function auditAllMetas(): Collection
    {
        $results = collect();

        SeoMeta::query()
            ->with('seoable')
            ->limit(1000)
            ->chunk(50, function ($metas) use (&$results) {
                foreach ($metas as $meta) {
                    $audit = $this->auditMeta($meta);
                    $results->push([
                        'id' => $meta->id,
                        'type' => class_basename($meta->seoable_type ?? ''),
                        'title' => $meta->title ?? '(sin título)',
                        'score' => $audit['score'],
                        'grade' => $audit['grade'],
                        'issues_count' => count($audit['issues']),
                        'issues' => $audit['issues'],
                    ]);
                }
            });

        return $results->sortBy('score');
    }

    private function buildResult(?string $url, array $checks): array
    {
        $score = 100;
        $issues = [];

        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $score -= $check['weight'];
                $issues[] = $check;
            } elseif ($check['status'] === 'warning') {
                $score -= (int) ($check['weight'] / 2);
                $issues[] = $check;
            }
        }

        $score = max(0, $score);
        $result = [
            'score' => $score,
            'grade' => $this->scoreToGrade($score),
            'issues' => $issues,
            'passed' => collect($checks)->where('status', 'ok')->values()->toArray(),
            'total_checks' => count($checks),
            'audited_at' => now()->toIso8601String(),
        ];

        if ($url !== null) {
            $result['url'] = $url;
        }

        return $result;
    }

    private function runChecks(string $html, string $url): array
    {
        return [
            ...$this->checkTitle($html),
            ...$this->checkDescription($html),
            ...$this->checkH1($html),
            ...$this->checkImages($html),
            ...$this->checkCanonical($html),
            ...$this->checkOpenGraph($html),
            ...$this->checkHttps($url),
            ...$this->checkStructuredData($html),
            ...$this->checkViewport($html),
            ...$this->checkTwitterCard($html),
            ...$this->checkH1Length($html),
            ...$this->checkImagesLazyLoading($html),
            ...$this->checkImagesDimensions($html),
            ...$this->checkRenderBlockingScripts($html),
            ...$this->checkCharset($html),
        ];
    }

    private function checkTitle(string $html): array
    {
        preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $match);
        $title = strip_tags($match[1] ?? '');
        $len = mb_strlen($title);

        if ($len === 0) {
            return [$this->issue('error', 'title_missing', 'Falta el título de la página', 'Agrega una etiqueta <title> con el título principal', 20)];
        }
        if ($len < 30) {
            return [$this->issue('warning', 'title_short', "Título muy corto ({$len} caracteres)", 'El título debe tener entre 30 y 60 caracteres', 10)];
        }
        if ($len > 60) {
            return [$this->issue('warning', 'title_long', "Título muy largo ({$len} caracteres)", 'Reduce el título a menos de 60 caracteres', 5)];
        }

        return [$this->ok('title_ok', "Título correcto ({$len} caracteres)")];
    }

    private function checkDescription(string $html): array
    {
        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/si', $html, $match);
        if (empty($match[1])) {
            preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\'][^>]*>/si', $html, $match);
        }
        $desc = $match[1] ?? '';
        $len = mb_strlen($desc);

        if ($len === 0) {
            return [$this->issue('error', 'description_missing', 'Falta la meta descripción', 'Agrega una meta descripción entre 120 y 160 caracteres', 15)];
        }
        if ($len < 80) {
            return [$this->issue('warning', 'description_short', "Meta descripción muy corta ({$len} caracteres)", 'Extiende la descripción a al menos 120 caracteres', 8)];
        }
        if ($len > 160) {
            return [$this->issue('warning', 'description_long', "Meta descripción muy larga ({$len} caracteres)", 'Reduce la descripción a menos de 160 caracteres', 5)];
        }

        return [$this->ok('description_ok', "Meta descripción correcta ({$len} caracteres)")];
    }

    private function checkH1(string $html): array
    {
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $html, $matches);
        $count = count($matches[0]);

        if ($count === 0) {
            return [$this->issue('error', 'h1_missing', 'Falta el encabezado H1', 'Agrega un H1 con la keyword principal', 15)];
        }
        if ($count > 1) {
            return [$this->issue('warning', 'h1_multiple', "Múltiples H1 ({$count}) encontrados", 'Usa solo un H1 por página', 8)];
        }

        return [$this->ok('h1_ok', 'H1 presente (1 único)')];
    }

    private function checkImages(string $html): array
    {
        preg_match_all('/<img[^>]*>/si', $html, $matches);
        $total = count($matches[0]);
        if ($total === 0) {
            return [];
        }

        $withoutAlt = collect($matches[0])
            ->filter(fn ($img) => ! preg_match('/alt=["\'][^"\']+["\']/i', $img))
            ->count();

        if ($withoutAlt > 0) {
            return [$this->issue('warning', 'images_alt', "{$withoutAlt} imágenes sin atributo alt", 'Agrega textos alt descriptivos a todas las imágenes', 8)];
        }

        return [$this->ok('images_alt_ok', "Todas las imágenes ({$total}) tienen alt")];
    }

    private function checkCanonical(string $html): array
    {
        $hasCanonical = str_contains($html, 'rel="canonical"') || str_contains($html, "rel='canonical'");

        return $hasCanonical
            ? [$this->ok('canonical_ok', 'URL canónica definida')]
            : [$this->issue('warning', 'canonical_missing', 'Falta la URL canónica', 'Agrega <link rel="canonical"> para evitar contenido duplicado', 5)];
    }

    private function checkOpenGraph(string $html): array
    {
        $hasOg = str_contains($html, 'og:title') && str_contains($html, 'og:description');

        return $hasOg
            ? [$this->ok('og_ok', 'Open Graph presente')]
            : [$this->issue('warning', 'og_missing', 'Faltan etiquetas Open Graph', 'Agrega og:title, og:description, og:image', 5)];
    }

    private function checkHttps(string $url): array
    {
        return str_starts_with($url, 'https://')
            ? [$this->ok('https_ok', 'La página usa HTTPS')]
            : [$this->issue('error', 'https_missing', 'La página no usa HTTPS', 'Migra a HTTPS — es un factor de ranking', 12)];
    }

    private function checkStructuredData(string $html): array
    {
        preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $matches);
        if (empty($matches[1])) {
            return [$this->issue('warning', 'schema_missing', 'No hay datos estructurados (JSON-LD)', 'Agrega Schema.org markup', 5)];
        }

        $invalid = 0;
        foreach ($matches[1] as $jsonRaw) {
            $decoded = json_decode(trim($jsonRaw), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $invalid++;
            }
        }

        if ($invalid > 0) {
            return [$this->issue('error', 'schema_invalid', "{$invalid} bloque(s) JSON-LD con sintaxis inválida", 'Verifica que el JSON-LD sea válido', 10)];
        }

        return [$this->ok('schema_ok', count($matches[1]).' bloque(s) JSON-LD válido(s)')];
    }

    private function checkViewport(string $html): array
    {
        return stripos($html, '<meta name="viewport"') !== false
            ? [$this->ok('viewport_ok', 'Meta viewport presente')]
            : [$this->issue('error', 'viewport_missing', 'Falta meta viewport', 'Agrega meta viewport para compatibilidad móvil', 10)];
    }

    private function checkTwitterCard(string $html): array
    {
        return str_contains($html, 'twitter:card')
            ? [$this->ok('twitter_card_ok', 'Twitter Card presente')]
            : [$this->issue('warning', 'twitter_card_missing', 'Faltan etiquetas Twitter Card', 'Agrega twitter:card y twitter:title', 5)];
    }

    private function checkH1Length(string $html): array
    {
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $html, $matches);
        if (count($matches[0]) !== 1) {
            return [];
        }

        $text = strip_tags($matches[1][0]);
        $len = mb_strlen(trim($text));

        if ($len < 10) {
            return [$this->issue('warning', 'h1_short', 'H1 muy corto', 'El H1 debe describir claramente el contenido', 5)];
        }
        if ($len > 70) {
            return [$this->issue('warning', 'h1_long', "H1 muy largo ({$len} caracteres)", 'Reduce el H1 a menos de 70 caracteres', 5)];
        }

        return [$this->ok('h1_length_ok', "Longitud de H1 correcta ({$len} caracteres)")];
    }

    private function checkImagesLazyLoading(string $html): array
    {
        preg_match_all('/<img[^>]*>/i', $html, $matches);
        $imgs = $matches[0] ?? [];
        if (count($imgs) <= 1) {
            return [];
        }

        $belowFold = array_slice($imgs, 2);
        $withoutLazy = array_filter($belowFold, fn ($img) => ! preg_match('/loading\s*=\s*["\']?(lazy)/i', $img));
        $missing = count($withoutLazy);

        if ($missing === 0) {
            return [$this->ok('lazy_loading_ok', 'Imágenes usan lazy-loading')];
        }

        return [$this->issue('warning', 'lazy_loading_missing', "{$missing} imagen(es) sin loading=\"lazy\"", 'Agrega loading="lazy" a imágenes fuera del viewport', 8)];
    }

    private function checkImagesDimensions(string $html): array
    {
        preg_match_all('/<img[^>]*>/i', $html, $matches);
        $imgs = $matches[0] ?? [];
        if (empty($imgs)) {
            return [];
        }

        $missing = count(array_filter($imgs, fn ($img) => ! preg_match('/\bwidth\s*=/i', $img) || ! preg_match('/\bheight\s*=/i', $img)));

        if ($missing === 0) {
            return [$this->ok('img_dimensions_ok', 'Todas las imágenes tienen width/height')];
        }

        return [$this->issue('warning', 'img_dimensions_missing', "{$missing} imagen(es) sin width/height (CLS)", 'Define width y height en cada img', 10)];
    }

    private function checkRenderBlockingScripts(string $html): array
    {
        preg_match('/<head[^>]*>(.*?)<\/head>/si', $html, $headMatch);
        $head = $headMatch[1] ?? '';
        preg_match_all('/<script[^>]*\bsrc=[^>]*>/i', $head, $matches);
        $blocking = array_filter($matches[0] ?? [], fn ($tag) => ! preg_match('/\b(async|defer|type\s*=\s*["\']module)/i', $tag));
        $count = count($blocking);

        if ($count === 0) {
            return [$this->ok('render_blocking_ok', 'No hay scripts bloqueantes en <head>')];
        }

        return [$this->issue('warning', 'render_blocking_scripts', "{$count} script(s) bloqueante(s) en <head>", 'Agrega defer o async a scripts en <head>', 8)];
    }

    private function checkCharset(string $html): array
    {
        $firstKb = substr($html, 0, 1024);
        if (preg_match('/<meta[^>]+charset=/i', $firstKb)) {
            return [$this->ok('charset_ok', 'Charset definido en los primeros 1024 bytes')];
        }

        return [$this->issue('warning', 'charset_missing', 'Charset no declarado al inicio del <head>', 'Agrega <meta charset="utf-8"> como primer elemento de <head>', 3)];
    }

    private function runMetaChecks(SeoMeta $meta): array
    {
        return [
            ...$this->checkMetaTitle($meta),
            ...$this->checkMetaDescription($meta),
            $this->checkMetaOgImage($meta),
            $this->checkMetaRobots($meta),
            $this->checkMetaKeywords($meta),
            $this->checkMetaTwitterCard($meta),
            $this->checkKeywordDensity($meta),
        ];
    }

    private function checkMetaTitle(SeoMeta $meta): array
    {
        $len = mb_strlen($meta->title ?? '');
        if ($len === 0) {
            return [$this->issue('error', 'title_missing', 'Falta el título SEO', 'Define un título SEO para esta página', 20)];
        }
        if ($len < 30) {
            return [$this->issue('warning', 'title_short', "Título muy corto ({$len} car.)", 'El título debe tener 30-60 caracteres', 10)];
        }
        if ($len > 60) {
            return [$this->issue('warning', 'title_long', "Título muy largo ({$len} car.)", 'Reduce a menos de 60 caracteres', 5)];
        }

        return [$this->ok('title_ok', "Título correcto ({$len} car.)")];
    }

    private function checkMetaDescription(SeoMeta $meta): array
    {
        $len = mb_strlen($meta->description ?? '');
        if ($len === 0) {
            return [$this->issue('error', 'description_missing', 'Falta la meta descripción', 'Define una descripción de 120-160 caracteres', 15)];
        }
        if ($len < 80) {
            return [$this->issue('warning', 'description_short', "Descripción corta ({$len} car.)", 'Extiende a al menos 120 caracteres', 8)];
        }
        if ($len > 160) {
            return [$this->issue('warning', 'description_long', "Descripción larga ({$len} car.)", 'Reduce a menos de 160 caracteres', 5)];
        }

        return [$this->ok('description_ok', "Descripción correcta ({$len} car.)")];
    }

    private function checkMetaOgImage(SeoMeta $meta): array
    {
        return ! empty($meta->og_image)
            ? $this->ok('og_image_ok', 'Imagen Open Graph definida')
            : $this->issue('warning', 'og_image_missing', 'Falta imagen Open Graph', 'Agrega una imagen 1200×630px', 5);
    }

    private function checkMetaRobots(SeoMeta $meta): array
    {
        $robots = $meta->robots ?? 'index, follow';

        return str_contains($robots, 'noindex')
            ? $this->issue('warning', 'noindex', 'Página marcada como noindex', 'Verifica si es intencional.', 0)
            : $this->ok('robots_ok', "Robots: {$robots}");
    }

    private function checkMetaKeywords(SeoMeta $meta): array
    {
        return ! empty($meta->keywords)
            ? $this->ok('keywords_ok', 'Palabras clave definidas')
            : $this->issue('warning', 'keywords_missing', 'Sin palabras clave', 'Agrega keywords relevantes', 3);
    }

    private function checkMetaTwitterCard(SeoMeta $meta): array
    {
        return $meta->twitter_card !== null
            ? $this->ok('twitter_card_ok', 'Twitter Card configurada')
            : $this->issue('warning', 'twitter_card_missing', 'Sin Twitter Card', 'Define el tipo de Twitter Card', 3);
    }

    private function checkKeywordDensity(SeoMeta $meta): array
    {
        $keywords = $meta->keywords ?? '';
        if (empty($keywords)) {
            return $this->ok('keyword_density_skip', 'Sin keywords para verificar densidad');
        }

        $keywordList = array_filter(
            array_map('trim', explode(',', strtolower($keywords))),
            fn ($k) => mb_strlen($k) > 2
        );

        if (empty($keywordList)) {
            return $this->ok('keyword_density_skip', 'Keywords muy cortas');
        }

        $searchText = strtolower(($meta->title ?? '').' '.($meta->description ?? ''));
        $missingKeywords = [];

        foreach (array_slice($keywordList, 0, 5) as $keyword) {
            if (! str_contains($searchText, $keyword)) {
                $missingKeywords[] = $keyword;
            }
        }

        if (count($missingKeywords) > 0) {
            return $this->issue('warning', 'keyword_density', 'Keywords no encontradas en título/descripción: '.implode(', ', $missingKeywords), 'Incluye tus keywords en el título y descripción', 5);
        }

        return $this->ok('keyword_density_ok', 'Keywords presentes en título y/o descripción');
    }

    private function issue(string $status, string $code, string $message, string $recommendation, int $weight): array
    {
        return compact('status', 'code', 'message', 'recommendation', 'weight');
    }

    private function ok(string $code, string $message): array
    {
        return ['status' => 'ok', 'code' => $code, 'message' => $message, 'recommendation' => '', 'weight' => 0];
    }

    private function scoreToGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 40 => 'D',
            default => 'F',
        };
    }

    private function validatePublicUrl(string $url): void
    {
        $parsed = parse_url($url);
        if (! in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            throw new \RuntimeException('Solo se permiten URLs http/https');
        }

        $host = $parsed['host'] ?? '';
        $ip = gethostbyname($host);
        $isPrivate = ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        if ($isPrivate) {
            throw new \RuntimeException('No se permiten URLs de redes privadas');
        }
    }

    private function checkReadability(string $text): array
    {
        $text = strip_tags($text);
        $sentences = max(1, preg_match_all('/[.!?]+/', $text, $m));
        $words = max(1, str_word_count($text));
        $syllables = max(1, preg_match_all('/[aeiouáéíóúüàèìòùâêîôû]+/u', $text, $m2));
        $score = 206.835 - (1.015 * ($words / $sentences)) - (84.6 * ($syllables / $words));
        $score = (int) max(0, min(100, round($score)));

        $level = match (true) {
            $score >= 70 => 'Fácil',
            $score >= 50 => 'Moderado',
            $score >= 30 => 'Difícil',
            default => 'Muy difícil',
        };

        return ['score' => $score, 'level' => $level, 'words' => $words, 'sentences' => $sentences];
    }

    private function errorResult(string $message): array
    {
        return [
            'url' => '',
            'score' => 0,
            'grade' => 'F',
            'issues' => [['status' => 'error', 'code' => 'access_error', 'message' => $message, 'recommendation' => '', 'weight' => 100]],
            'passed' => [],
            'total_checks' => 1,
            'audited_at' => now()->toIso8601String(),
        ];
    }
}
