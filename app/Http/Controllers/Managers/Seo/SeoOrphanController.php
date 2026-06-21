<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Bundle\Bundle;
use App\Models\Certifier;
use App\Models\Course\Course;
use App\Models\Instruction\Instruction;
use App\Models\Seo\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoOrphanController extends Controller
{
    /**
     * Clases permitidas para generación de SeoMeta (whitelist de seguridad).
     */
    private const ALLOWED_MODELS = [
        'App\Models\Course\Course' => Course::class,
        'App\Models\Blog\Blog' => Blog::class,
        'App\Models\Bundle\Bundle' => Bundle::class,
        'App\Models\Instruction\Instruction' => Instruction::class,
        'App\Models\Certifier' => Certifier::class,
    ];

    public function index(Request $request): View
    {

        $orphans = collect();

        // Courses sin SeoMeta
        $courses = Course::query()
            ->whereDoesntHave('seoMeta')
            ->select('id', 'title', 'slack')
            ->get()
            ->map(fn (Course $course) => [
                'type' => 'Course',
                'id' => $course->id,
                'title' => $course->title,
                'url' => route('courses.view', $course->slack),
                'model_class' => Course::class,
            ]);

        // Blogs sin SeoMeta
        $blogs = Blog::query()
            ->whereDoesntHave('seoMeta')
            ->select('id', 'title', 'slack', 'slug')
            ->get()
            ->map(fn (Blog $blog) => [
                'type' => 'Blog',
                'id' => $blog->id,
                'title' => $blog->title,
                'url' => route('blogs.view', $blog->slug),
                'model_class' => Blog::class,
            ]);

        // Bundles sin SeoMeta
        $bundles = Bundle::query()
            ->whereDoesntHave('seoMeta')
            ->select('id', 'title', 'slack', 'slug')
            ->get()
            ->map(fn (Bundle $bundle) => [
                'type' => 'Bundle',
                'id' => $bundle->id,
                'title' => $bundle->title,
                'url' => route('bundles.view', $bundle->slug),
                'model_class' => Bundle::class,
            ]);

        // Instructions sin SeoMeta
        $instructions = Instruction::query()
            ->whereDoesntHave('seoMeta')
            ->select('id', 'title', 'slack')
            ->get()
            ->map(fn (Instruction $instruction) => [
                'type' => 'Instruction',
                'id' => $instruction->id,
                'title' => $instruction->title,
                'url' => null,
                'model_class' => Instruction::class,
            ]);

        // Certifiers sin SeoMeta
        $certifiers = Certifier::query()
            ->whereDoesntHave('seoMeta')
            ->select('id', 'firstname', 'lastname', 'slack')
            ->get()
            ->map(fn (Certifier $certifier) => [
                'type' => 'Certifier',
                'id' => $certifier->id,
                'title' => trim("{$certifier->firstname} {$certifier->lastname}"),
                'url' => route('certifiers.view', $certifier->slack),
                'model_class' => Certifier::class,
            ]);

        $orphans = $orphans
            ->concat($courses)
            ->concat($blogs)
            ->concat($bundles)
            ->concat($instructions)
            ->concat($certifiers);

        $counts = [
            'Course' => $courses->count(),
            'Blog' => $blogs->count(),
            'Bundle' => $bundles->count(),
            'Instruction' => $instructions->count(),
            'Certifier' => $certifiers->count(),
        ];

        $total = $orphans->count();

        return view('managers.views.seo.orphans.index', compact('orphans', 'counts', 'total'));
    }

    public function generate(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'model_class' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ]);

        $modelClass = $validated['model_class'];
        $modelId = (int) $validated['model_id'];

        if (! array_key_exists($modelClass, self::ALLOWED_MODELS)) {
            return response()->json([
                'success' => false,
                'message' => 'Clase de modelo no permitida.',
            ], 422);
        }

        $model = $modelClass::find($modelId);

        if (! $model) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado.',
            ], 404);
        }

        $title = $model->title ?? $model->name ?? '';

        SeoMeta::firstOrCreate(
            [
                'seoable_type' => $modelClass,
                'seoable_id' => $modelId,
            ],
            [
                'title' => $title,
                'robots' => 'index,follow',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Meta SEO generada correctamente.',
        ]);
    }

    public function bulkGenerate(Request $request): JsonResponse
    {

        $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.model_class' => ['required_with:items', 'string'],
            'items.*.model_id' => ['required_with:items', 'integer'],
        ]);

        // Sin items = generar para todos los modelos huérfanos
        $items = $request->input('items');
        if (empty($items)) {
            $items = [];
            foreach (array_keys(self::ALLOWED_MODELS) as $modelClass) {
                $modelClass::whereDoesntHave('seoMeta')->select('id')->get()
                    ->each(function ($m) use ($modelClass, &$items) {
                        $items[] = ['model_class' => $modelClass, 'model_id' => $m->id];
                    });
            }
        }

        $created = 0;

        foreach ($items as $item) {
            $modelClass = $item['model_class'];
            $modelId = (int) $item['model_id'];

            if (! array_key_exists($modelClass, self::ALLOWED_MODELS)) {
                continue;
            }

            $model = $modelClass::find($modelId);

            if (! $model) {
                continue;
            }

            $title = $model->title ?? $model->name ?? '';

            $record = SeoMeta::firstOrCreate(
                [
                    'seoable_type' => $modelClass,
                    'seoable_id' => $modelId,
                ],
                [
                    'title' => $title,
                    'robots' => 'index,follow',
                ]
            );

            if ($record->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se generaron {$created} registros SEO.",
            'created' => $created,
        ]);
    }
}
