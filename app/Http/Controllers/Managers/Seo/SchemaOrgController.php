<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchemaOrgController extends Controller
{
    private const TEMPLATES = [
        'Article' => [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => '',
            'description' => '',
            'image' => '',
            'datePublished' => '',
            'dateModified' => '',
            'author' => ['@type' => 'Person', 'name' => ''],
        ],
        'Product' => [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => '',
            'description' => '',
            'image' => '',
            'sku' => '',
            'brand' => ['@type' => 'Brand', 'name' => ''],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0.00',
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ],
        ],
        'FAQPage' => [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => '¿Pregunta ejemplo?',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Respuesta ejemplo.'],
                ],
            ],
        ],
        'Event' => [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => '',
            'startDate' => '',
            'endDate' => '',
            'location' => ['@type' => 'Place', 'name' => '', 'address' => ''],
            'description' => '',
        ],
        'HowTo' => [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => '',
            'description' => '',
            'totalTime' => 'PT30M',
            'step' => [],
        ],
        'WebPage' => [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => '',
            'description' => '',
            'url' => '',
        ],
        'Course' => [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => '',
            'description' => '',
            'provider' => ['@type' => 'Organization', 'name' => ''],
        ],
    ];

    public function edit(SeoMeta $seoMeta): View
    {
        $types = array_keys(self::TEMPLATES);
        $templates = self::TEMPLATES;
        $currentType = $seoMeta->schema_type;
        $currentSchema = $seoMeta->schema_custom;

        return view('managers.views.seo.schema-org.edit', compact('seoMeta', 'types', 'templates', 'currentType', 'currentSchema'));
    }

    public function update(Request $request, SeoMeta $seoMeta): RedirectResponse
    {
        $request->validate([
            'schema_type' => ['nullable', 'string', 'in:Article,Product,FAQPage,Event,HowTo,WebPage,Course'],
            'schema_custom' => ['nullable', 'string'],
        ]);

        $schema = $request->input('schema_custom');
        $decoded = $schema ? json_decode($schema, true) : null;

        $seoMeta->update([
            'schema_type' => $request->input('schema_type') ?: null,
            'schema_custom' => $decoded,
        ]);

        return redirect()->route('manager.seo.metas.edit', $seoMeta)
            ->with('success', 'Schema.org actualizado correctamente.');
    }

    public function template(string $type): JsonResponse
    {
        if (! isset(self::TEMPLATES[$type])) {
            return response()->json(['error' => 'Tipo desconocido'], 404);
        }

        return response()->json([
            'type' => $type,
            'template' => self::TEMPLATES[$type],
        ]);
    }

    public function validateJson(Request $request): JsonResponse
    {
        $schema = $request->input('schema_custom');
        $errors = [];

        $decoded = json_decode($schema ?? '', true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'valid' => false,
                'errors' => ['JSON inválido: '.json_last_error_msg()],
            ]);
        }

        if (! is_array($decoded)) {
            return response()->json([
                'valid' => false,
                'errors' => ['El schema debe ser un objeto JSON.'],
            ]);
        }

        if (! isset($decoded['@context'])) {
            $errors[] = 'Falta `@context` (debe ser "https://schema.org").';
        } elseif ($decoded['@context'] !== 'https://schema.org') {
            $errors[] = '`@context` debe ser "https://schema.org".';
        }

        if (! isset($decoded['@type'])) {
            $errors[] = 'Falta `@type` (ej: "Article", "Product", "FAQPage").';
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
            'preview' => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function bulkApply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'max:200'],
            'schema_type' => ['required', 'string', 'in:Article,Product,FAQPage,Event,HowTo,WebPage,Course'],
            'force' => ['sometimes', 'boolean'],
        ]);

        $query = SeoMeta::query()->where('seoable_type', $data['model_type']);

        if (! ($data['force'] ?? false)) {
            $query->whereNull('schema_type');
        }

        $updated = $query->update(['schema_type' => $data['schema_type']]);

        return response()->json([
            'updated' => $updated,
            'message' => "Se aplicó schema \"{$data['schema_type']}\" a {$updated} registros.",
        ]);
    }
}
