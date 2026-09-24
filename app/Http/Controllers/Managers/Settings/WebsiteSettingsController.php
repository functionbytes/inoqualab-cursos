<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateWebsiteSettingsRequest;
use App\Models\Course\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WebsiteSettingsController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('settings.update'), 403);

        // Curso real para la vista previa de cada diseño de tarjeta: se prefiere
        // uno en promoción para que se vea también el precio tachado.
        $baseQuery = fn () => Course::query()
            ->available()->website()
            ->with(['categorie', 'media'])
            ->withCount(['lessons', 'chapters']);

        $previewCourse = $baseQuery()
            ->where('payment', 1)
            ->where('promotion', 1)
            ->whereColumn('discount', '<', 'price')
            ->latest()
            ->first() ?? $baseQuery()->latest()->first();

        return view('managers.views.settings.website.setting', [
            'previewCourse' => $previewCourse,
        ]);
    }

    public function update(UpdateWebsiteSettingsRequest $request): JsonResponse
    {
        updateSettings($request->safe()->only(['pages_course_card_variant', 'pages_course_detail_variant', 'pages_about_variant']));

        return response()->json([
            'success' => true,
            'message' => 'Se actualizó el diseño del sitio web',
        ]);
    }
}
