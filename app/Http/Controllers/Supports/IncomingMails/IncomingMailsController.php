<?php

namespace App\Http\Controllers\Supports\IncomingMails;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseAlias;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseAlias;
use App\Models\Mail\IncomingMail;
use App\Services\IncomingMail\CourseMatcher;
use App\Services\IncomingMail\EnterpriseMatcher;
use App\Services\IncomingMail\OrderCreator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomingMailsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $mails = IncomingMail::query()
            ->with('enterprise', 'order')
            ->descending();

        if ($status !== null && $status !== '') {
            $mails = $mails->status($status);
        } else {
            $mails = $mails->whereIn('status', [
                IncomingMail::STATUS_PENDING_REVIEW,
                IncomingMail::STATUS_FAILED,
            ]);
        }

        $mails = $mails->paginate(paginationNumber());

        $counts = IncomingMail::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('supports.views.mails.index')->with([
            'mails' => $mails,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    public function show($slack)
    {
        $mail = IncomingMail::slack($slack);

        $payload = $mail->parsed_payload ?? [];
        $enterprise = $mail->enterprise;
        $enterprises = Enterprise::query()->descending()->get(['id', 'title']);

        $courseMatcher = new CourseMatcher;

        $courseMatches = collect($payload['courses'] ?? [])->map(function (string $courseText) use ($courseMatcher, $enterprise) {
            $matched = $courseMatcher->match($courseText, $enterprise);

            return [
                'text' => $courseText,
                'matched' => $matched,
            ];
        });

        $enterpriseCourses = $enterprise
            ? $enterprise->courses()->reorder('courses.updated_at', 'desc')->get(['courses.id', 'courses.title'])
            : collect();

        return view('supports.views.mails.view')->with([
            'mail' => $mail,
            'payload' => $payload,
            'enterprise' => $enterprise,
            'enterprises' => $enterprises,
            'courseMatches' => $courseMatches,
            'enterpriseCourses' => $enterpriseCourses,
        ]);
    }

    public function getCourses(Request $request): JsonResponse
    {
        if ($request->enterprise_id === null) {
            return response()->json([]);
        }

        $enterprise = Enterprise::id($request->enterprise_id);

        if (! $enterprise instanceof Enterprise) {
            return response()->json([]);
        }

        $courses = $enterprise->courses()
            ->available()
            ->reorder('courses.updated_at', 'desc')
            ->get(['courses.id', 'courses.title']);

        $formatted = $courses->map(fn ($c) => ['id' => $c->id, 'text' => $c->title])->values();

        return response()->json($formatted);
    }

    public function confirm(Request $request, $slack): JsonResponse
    {
        $mail = IncomingMail::slack($slack);

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.']);
        }

        $enterpriseId = $request->input('enterprise_id');
        $courseMap = $request->input('course_map', []); // ['texto del curso' => course_id, ...]
        $saveAlias = (bool) $request->input('save_alias', false);

        $enterprise = Enterprise::id($enterpriseId);

        if (! $enterprise instanceof Enterprise) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada.']);
        }

        $courseIds = array_values(array_unique(array_filter(array_values($courseMap))));

        if (empty($courseIds)) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un curso.']);
        }

        // Restringe a cursos que realmente pertenecen a la empresa para evitar
        // mapear a cursos ajenos por error del operador o payload manipulado.
        $courses = $enterprise->courses()
            ->whereIn('courses.id', $courseIds)
            ->get();

        if ($courses->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los cursos seleccionados.']);
        }

        if ($courses->count() !== count($courseIds)) {
            return response()->json(['success' => false, 'message' => 'Algún curso seleccionado no pertenece a la empresa.']);
        }

        try {
            $order = (new OrderCreator)->createFromPayload(
                $mail,
                $mail->parsed_payload ?? [],
                $enterprise,
                $courses->all(),
                Auth::id()
            );

            DB::transaction(function () use ($mail, $enterprise, $order, $saveAlias, $courseMap, $courses) {
                $mail->status = IncomingMail::STATUS_PROCESSED;
                $mail->matched_enterprise_id = $enterprise->id;
                $mail->order_id = $order?->id;
                $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
                $mail->save();

                if (! $saveAlias) {
                    return;
                }

                $this->persistEnterpriseAlias($mail, $enterprise);
                $this->persistCourseAliases($courseMap, $courses);
            });

            return response()->json([
                'success' => true,
                'message' => $order !== null
                    ? 'Orden creada correctamente.'
                    : 'Correo procesado (sin cursos nuevos para inscribir).',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function discard($slack): JsonResponse
    {
        $mail = IncomingMail::slack($slack);

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.']);
        }

        $mail->status = IncomingMail::STATUS_IGNORED;
        $mail->save();

        return response()->json(['success' => true, 'message' => 'Correo descartado correctamente.']);
    }

    public function bulkDiscard(Request $request): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:discard'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:incoming_mails,id'],
        ]);

        // Misma lógica que discard(): solo marca como ignorados los que aún
        // no lo estén, sin tocar correos ya procesados de otra forma.
        $count = IncomingMail::whereIn('id', $request->ids)
            ->where('status', '!=', IncomingMail::STATUS_IGNORED)
            ->update(['status' => IncomingMail::STATUS_IGNORED]);

        return response()->json(['success' => true, 'message' => $count.' correo(s) descartados.']);
    }

    private function persistEnterpriseAlias(IncomingMail $mail, Enterprise $enterprise): void
    {
        $payload = $mail->parsed_payload ?? [];

        $enterpriseMatcher = new EnterpriseMatcher;

        if (isset($payload['enterprise_code']) && $payload['enterprise_code'] !== '') {
            EnterpriseAlias::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'alias_type' => EnterpriseAlias::TYPE_CODE,
                    'normalized_value' => $enterpriseMatcher->normalize($payload['enterprise_code']),
                ],
                [
                    'alias_value' => $payload['enterprise_code'],
                ]
            );
        }

        if (isset($payload['enterprise_name']) && $payload['enterprise_name'] !== '') {
            EnterpriseAlias::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'alias_type' => EnterpriseAlias::TYPE_NAME,
                    'normalized_value' => $enterpriseMatcher->normalize($payload['enterprise_name']),
                ],
                [
                    'alias_value' => $payload['enterprise_name'],
                ]
            );
        }
    }

    private function persistCourseAliases(array $courseMap, $courses): void
    {
        $courseMatcher = new CourseMatcher;

        $coursesById = $courses->keyBy('id');

        foreach ($courseMap as $courseText => $courseId) {
            if ($courseId === null || $courseId === '') {
                continue;
            }

            $course = $coursesById->get($courseId);

            if (! $course instanceof Course) {
                continue;
            }

            $normalized = $courseMatcher->normalize((string) $courseText);

            CourseAlias::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'normalized_alias' => $normalized,
                ],
                [
                    'alias' => (string) $courseText,
                    'source' => 'manual',
                ]
            );
        }
    }
}
