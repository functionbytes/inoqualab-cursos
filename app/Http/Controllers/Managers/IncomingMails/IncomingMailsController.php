<?php

namespace App\Http\Controllers\Managers\IncomingMails;

use App\Http\Controllers\Controller;
use App\Mail\IncomingMails\MailProcessedNotificationMail;
use App\Models\Course\Course;
use App\Models\Course\CourseAlias;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseAlias;
use App\Models\Mail\IncomingMail;
use App\Models\Mail\MailAutoConfirmRule;
use App\Models\User;
use App\Services\IncomingMail\CourseMatcher;
use App\Services\IncomingMail\EnterpriseMatcher;
use App\Services\IncomingMail\OrderCreator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomingMailsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $enterpriseId = $request->input('enterprise_id', '');
        $confidence = $request->input('confidence', '');
        $assignedTo = $request->input('assigned_to', '');

        $allowedSorts = ['received_at', 'confidence_score'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'received_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $allowedPerPage = [15, 25, 50];
        $perPage = in_array((int) $request->input('per_page'), $allowedPerPage)
            ? (int) $request->input('per_page')
            : paginationNumber();

        $mails = IncomingMail::query()
            ->with('enterprise', 'order', 'assignedUser')
            ->orderBy($sort, $direction)
            ->when($search !== '', fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('from', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('parsed_payload', 'like', "%{$search}%");
            }))
            ->when($dateFrom !== '' && $dateTo !== '', fn ($q) => $q->whereBetween('received_at', [
                $dateFrom.' 00:00:00',
                $dateTo.' 23:59:59',
            ]))
            ->when($enterpriseId !== '', fn ($q) => $q->where('matched_enterprise_id', $enterpriseId))
            ->when($confidence === 'high', fn ($q) => $q->where('confidence_score', '>=', 90))
            ->when($confidence === 'medium', fn ($q) => $q->whereBetween('confidence_score', [50, 89]))
            ->when($confidence === 'low', fn ($q) => $q->where('confidence_score', '<', 50)->whereNotNull('confidence_score'))
            ->when($assignedTo === 'me', fn ($q) => $q->where('assigned_to', Auth::id()))
            ->when($assignedTo === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->when(is_numeric($assignedTo), fn ($q) => $q->where('assigned_to', (int) $assignedTo));

        if ($status !== null && $status !== '') {
            $mails = $mails->status($status);
        } else {
            $mails = $mails->whereIn('status', [
                IncomingMail::STATUS_PENDING_REVIEW,
                IncomingMail::STATUS_FAILED,
            ]);
        }

        $mails = $mails->paginate($perPage);

        $counts = IncomingMail::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statsRow = DB::table('incoming_mails')->selectRaw(
            "COUNT(CASE WHEN status = 'pending_review' AND DATE(received_at) = CURDATE() THEN 1 END) as pending_today,
             COUNT(CASE WHEN status = 'pending_review' AND DATE(received_at) = CURDATE() - INTERVAL 1 DAY THEN 1 END) as pending_yesterday,
             COUNT(CASE WHEN status = 'processed' AND DATE(processed_at) = CURDATE() THEN 1 END) as processed_today,
             COUNT(CASE WHEN status = 'processed' AND DATE(processed_at) = CURDATE() - INTERVAL 1 DAY THEN 1 END) as processed_yesterday,
             COUNT(CASE WHEN status = 'failed' AND updated_at BETWEEN ? AND ? THEN 1 END) as failed_week,
             COUNT(CASE WHEN status IN ('pending_review','failed') THEN 1 END) as unresolved,
             ROUND(AVG(CASE WHEN status = 'processed' AND DATE(processed_at) = CURDATE() AND processed_at IS NOT NULL AND received_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, received_at, processed_at) END)) as avg_minutes_today",
            [now()->startOfWeek(), now()->endOfWeek()]
        )->first();
        $stats = (array) $statsRow;

        if ($request->ajax()) {
            return view('managers.views.mails._rows', compact('mails'));
        }

        // Chart: actividad de los últimos 7 días
        $rawChart = DB::table('incoming_mails')
            ->selectRaw('DATE(received_at) as date, status, COUNT(*) as count')
            ->where('received_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date', 'status')
            ->get()
            ->groupBy('date');

        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $day = $rawChart->get($date, collect());
            $chartData->push([
                'date' => $date,
                'received' => $day->sum('count'),
                'processed' => (int) $day->firstWhere('status', 'processed')?->count,
                'failed' => (int) $day->firstWhere('status', 'failed')?->count,
                'pending' => (int) $day->firstWhere('status', 'pending_review')?->count,
            ]);
        }

        $reviewers = User::query()
            ->whereIn('id', IncomingMail::query()->distinct()->whereNotNull('assigned_to')->pluck('assigned_to'))
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname']);

        $selectedReviewer = is_numeric($assignedTo)
            ? $reviewers->firstWhere('id', (int) $assignedTo)
            : null;

        return view('managers.views.mails.index')->with([
            'mails' => $mails,
            'counts' => $counts,
            'status' => $status,
            'stats' => $stats,
            'enterprise_id' => $enterpriseId,
            'selectedEnterprise' => $enterpriseId !== '' ? Enterprise::id($enterpriseId) : null,
            'sort' => $sort,
            'direction' => $direction,
            'confidence' => $confidence,
            'perPage' => $perPage,
            'assignedTo' => $assignedTo,
            'chartData' => $chartData,
            'reviewers' => $reviewers,
            'selectedReviewer' => $selectedReviewer,
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
            return [
                'text' => $courseText,
                'matched' => $courseMatcher->match($courseText, $enterprise),
            ];
        });

        $enterpriseCourses = $enterprise
            ? $enterprise->courses()->reorder('courses.updated_at', 'desc')->get(['courses.id', 'courses.title'])
            : collect();

        $timeline = Activity::query()
            ->with('causer')
            ->where('subject_type', IncomingMail::class)
            ->where('subject_id', $mail->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $duplicates = IncomingMail::query()
            ->where('from', $mail->from)
            ->where('id', '!=', $mail->id)
            ->where('received_at', '>=', now()->subHours(48))
            ->select('slack', 'subject', 'received_at', 'status')
            ->orderByDesc('received_at')
            ->limit(5)
            ->get();

        return view('managers.views.mails.view')->with([
            'mail' => $mail,
            'payload' => $payload,
            'enterprise' => $enterprise,
            'enterprises' => $enterprises,
            'courseMatches' => $courseMatches,
            'enterpriseCourses' => $enterpriseCourses,
            'timeline' => $timeline,
            'duplicates' => $duplicates,
        ]);
    }

    public function getCourses(Request $request): JsonResponse
    {
        if ($request->enterprise_id === null) {
            return response()->json([]);
        }

        // where()->first(), no Enterprise::id() (que aborta 404 con una página
        // cruda): este endpoint AJAX espera JSON.
        $enterprise = Enterprise::where('id', $request->enterprise_id)->first();

        if (! $enterprise instanceof Enterprise) {
            return response()->json([]);
        }

        $courses = $enterprise->courses()
            ->available()
            ->reorder('courses.updated_at', 'desc')
            ->get(['courses.id', 'courses.title']);

        return response()->json(
            $courses->map(fn ($c) => ['id' => $c->id, 'text' => $c->title])->values()
        );
    }

    public function getReviewers(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $sub->where('firstname', 'like', "%{$q}%")
                    ->orWhere('lastname', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            }))
            ->orderBy('firstname')
            ->limit(30)
            ->get(['id', 'firstname', 'lastname']);

        return response()->json(
            $users->map(fn ($u) => ['id' => $u->id, 'text' => trim("{$u->firstname} {$u->lastname}")])->values()
        );
    }

    public function getEnterprises(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        $enterprises = Enterprise::query()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->descending()
            ->limit(50)
            ->get(['id', 'title']);

        return response()->json(
            $enterprises->map(fn ($e) => ['id' => $e->id, 'text' => $e->title])->values()
        );
    }

    public function confirm(Request $request, $slack): JsonResponse
    {
        // where()->first() en vez de IncomingMail::slack() (que aborta 404 con
        // una página cruda): este endpoint AJAX espera JSON, y el check de
        // abajo necesita que $mail pueda ser null para que se ejecute.
        $mail = IncomingMail::where('slack', $slack)->first();

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.']);
        }

        if ($request->boolean('quick_confirm')) {
            $isQuickConfirm = true;
            if (! $mail->enterprise instanceof Enterprise) {
                return response()->json(['success' => false, 'message' => 'No hay empresa asociada para confirmación rápida.']);
            }
            $enterpriseId = $mail->matched_enterprise_id;
            $courseMatcher = new CourseMatcher;
            $payload = $mail->parsed_payload ?? [];
            $courseMap = [];
            foreach ($payload['courses'] ?? [] as $courseText) {
                $matched = $courseMatcher->match($courseText, $mail->enterprise);
                if ($matched) {
                    $courseMap[$courseText] = $matched->id;
                }
            }
            $saveAlias = false;
        } else {
            $isQuickConfirm = false;
            $enterpriseId = $request->input('enterprise_id');
            $courseMap = $request->input('course_map', []);
            $saveAlias = (bool) $request->input('save_alias', false);
            $payloadOverrides = array_filter(
                (array) $request->input('payload_overrides', []),
                fn ($v) => $v !== '' && $v !== null
            );
            $payload = array_merge($mail->parsed_payload ?? [], $payloadOverrides);
        }

        $enterprise = Enterprise::id($enterpriseId);

        if (! $enterprise instanceof Enterprise) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada.']);
        }

        $courseIds = array_values(array_unique(array_filter(array_values($courseMap))));

        if (empty($courseIds) && ! $isQuickConfirm) {
            return response()->json(['success' => false, 'message' => 'Debe seleccionar al menos un curso.']);
        }

        if (empty($courseIds) && $isQuickConfirm) {
            try {
                DB::transaction(function () use ($mail, $enterprise) {
                    $mail->status = IncomingMail::STATUS_PROCESSED;
                    $mail->matched_enterprise_id = $enterprise->id;
                    $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
                    $mail->save();
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Correo procesado. No hay alias de cursos registrados para esta empresa.',
                    'order_slack' => null,
                ]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        $courses = $enterprise->courses()->whereIn('courses.id', $courseIds)->get();

        if ($courses->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron los cursos seleccionados.']);
        }

        if ($courses->count() !== count($courseIds)) {
            return response()->json(['success' => false, 'message' => 'Algún curso seleccionado no pertenece a la empresa.']);
        }

        try {
            $order = DB::transaction(function () use ($mail, $enterprise, $payload, $courses, $saveAlias, $courseMap) {
                $created = (new OrderCreator)->createFromPayload(
                    $mail,
                    $payload,
                    $enterprise,
                    $courses->all(),
                    Auth::id()
                );

                $mail->status = IncomingMail::STATUS_PROCESSED;
                $mail->matched_enterprise_id = $enterprise->id;
                $mail->order_id = $created?->id;
                $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
                $mail->save();

                if ($saveAlias) {
                    $this->persistEnterpriseAlias($mail, $enterprise, $payload);
                    $this->persistCourseAliases($courseMap, $courses);
                }

                return $created;
            });

            if ($order !== null) {
                try {
                    $mail->load('enterprise', 'order');
                    Mail::queue(new MailProcessedNotificationMail($mail));
                } catch (\Throwable) {
                    // Silently ignore mail send failures
                }
            }

            return response()->json([
                'success' => true,
                'message' => $order !== null
                    ? 'Orden creada correctamente.'
                    : 'Correo procesado (sin cursos nuevos para inscribir).',
                'order_slack' => $order?->slack,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function discard($slack): JsonResponse
    {
        // where()->first(), no IncomingMail::slack() -- ver comentario en confirm().
        $mail = IncomingMail::where('slack', $slack)->first();

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.']);
        }

        $mail->status = IncomingMail::STATUS_IGNORED;
        $mail->save();

        return response()->json(['success' => true, 'message' => 'Correo descartado correctamente.']);
    }

    public function assign(Request $request, $slack): JsonResponse
    {
        $mail = IncomingMail::slack($slack);
        $userId = $request->input('user_id');

        $mail->assigned_to = $userId ?: null;
        $mail->save();

        $mail->load('assignedUser');
        $name = $mail->assignedUser ? trim($mail->assignedUser->firstname.' '.$mail->assignedUser->lastname) : null;

        return response()->json([
            'success' => true,
            'message' => $name ? "Asignado a {$name}." : 'Asignación removida.',
            'assigned_name' => $name,
        ]);
    }

    public function saveNote(Request $request, $slack): JsonResponse
    {
        $mail = IncomingMail::slack($slack);
        $mail->notes = trim($request->input('notes', ''));
        $mail->save();

        return response()->json(['success' => true, 'message' => 'Nota guardada.']);
    }

    public function reparse($slack): JsonResponse
    {
        // where()->first(), no IncomingMail::slack() -- ver comentario en confirm().
        $mail = IncomingMail::where('slack', $slack)->first();

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.']);
        }

        try {
            $enterpriseMatcher = new EnterpriseMatcher;
            $courseMatcher = new CourseMatcher;

            $payload = $mail->parsed_payload ?? [];
            $enterprise = $enterpriseMatcher->match(
                $payload['enterprise_code'] ?? null,
                $payload['enterprise_name'] ?? null
            );

            $courseMatches = array_map(
                fn (string $txt) => $courseMatcher->match($txt, $enterprise),
                $payload['courses'] ?? []
            );

            $allMatched = count($payload['courses'] ?? []) > 0
                && ! in_array(null, $courseMatches, true);

            $mail->matched_enterprise_id = $enterprise?->id;
            $mail->status = ($enterprise instanceof Enterprise && $allMatched)
                ? IncomingMail::STATUS_PENDING_REVIEW
                : $mail->status;
            $mail->save();

            // Auto-confirm check
            if ($enterprise instanceof Enterprise && $allMatched) {
                $rule = MailAutoConfirmRule::query()
                    ->where('enterprise_id', $enterprise->id)
                    ->where('is_active', true)
                    ->first();

                if ($rule instanceof MailAutoConfirmRule && $mail->confidence_score >= $rule->min_confidence) {
                    try {
                        $courseIds = array_values(array_filter(array_map(fn ($m) => $m?->id, $courseMatches)));
                        $courses = $enterprise->courses()->whereIn('courses.id', $courseIds)->get();

                        $order = DB::transaction(function () use ($mail, $enterprise, $courses, $payload) {
                            $created = (new OrderCreator)->createFromPayload(
                                $mail, $payload, $enterprise, $courses->all(), Auth::id()
                            );

                            $mail->status = IncomingMail::STATUS_PROCESSED;
                            $mail->matched_enterprise_id = $enterprise->id;
                            $mail->order_id = $created?->id;
                            $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
                            $mail->save();

                            return $created;
                        });

                        if ($order !== null) {
                            try {
                                $mail->load('enterprise', 'order');
                                Mail::queue(new MailProcessedNotificationMail($mail));
                            } catch (\Throwable) {
                                // Silently ignore mail send failures
                            }
                        }

                        return response()->json([
                            'success' => true,
                            'message' => "Auto-confirmado por regla de {$enterprise->title}.",
                            'auto_confirmed' => true,
                            'enterprise_id' => $enterprise->id,
                            'enterprise_name' => $enterprise->title,
                            'order_slack' => $order?->slack,
                            'all_matched' => $allMatched,
                            'course_matches' => collect($payload['courses'] ?? [])->zip($courseMatches)->map(
                                fn ($pair) => ['text' => $pair[0], 'matched_id' => $pair[1]?->id, 'matched_title' => $pair[1]?->title]
                            )->values(),
                        ]);
                    } catch (\Throwable) {
                        // Fall through to normal response if auto-confirm fails
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Re-análisis completado.',
                'auto_confirmed' => false,
                'enterprise_id' => $enterprise?->id,
                'enterprise_name' => $enterprise?->title,
                'all_matched' => $allMatched,
                'course_matches' => collect($payload['courses'] ?? [])->zip($courseMatches)->map(
                    fn ($pair) => ['text' => $pair[0], 'matched_id' => $pair[1]?->id, 'matched_title' => $pair[1]?->title]
                )->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function preview($slack): JsonResponse
    {
        // where()->first(), no IncomingMail::slack() -- ver comentario en confirm().
        $mail = IncomingMail::where('slack', $slack)->first();

        if (! $mail instanceof IncomingMail) {
            return response()->json(['success' => false]);
        }

        $enterpriseStats = null;
        if ($mail->matched_enterprise_id) {
            $row = DB::table('incoming_mails')
                ->where('matched_enterprise_id', $mail->matched_enterprise_id)
                ->selectRaw("COUNT(*) as total,
                             SUM(status='processed') as processed,
                             SUM(status='failed') as failed,
                             SUM(status='pending_review') as pending")
                ->first();
            $enterpriseStats = [
                'total' => (int) $row->total,
                'processed' => (int) $row->processed,
                'failed' => (int) $row->failed,
                'pending' => (int) $row->pending,
                'success_rate' => $row->total > 0 ? round($row->processed / $row->total * 100) : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'from' => $mail->from,
            'subject' => $mail->subject,
            'received_at' => Carbon::parse($mail->received_at)->format('d/m/Y H:i'),
            'status' => $mail->status,
            'enterprise' => $mail->enterprise?->title,
            'confidence_score' => $mail->confidence_score,
            'raw_body' => $mail->raw_body,
            'error_log' => $mail->error_log,
            'enterprise_stats' => $enterpriseStats,
        ]);
    }

    public function createAlias(Request $request): JsonResponse
    {
        $type = $request->input('type');
        $text = trim($request->input('text', ''));
        $targetId = $request->input('target_id');

        if ($text === '' || ! $targetId) {
            return response()->json(['success' => false, 'message' => 'Parámetros incompletos.']);
        }

        if ($type === 'enterprise') {
            // where()->first(), no Enterprise::id() -- ver comentario en getCourses().
            $enterprise = Enterprise::where('id', $targetId)->first();
            if (! $enterprise instanceof Enterprise) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada.']);
            }
            $matcher = new EnterpriseMatcher;
            EnterpriseAlias::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'alias_type' => EnterpriseAlias::TYPE_CODE,
                    'normalized_value' => $matcher->normalize($text),
                ],
                ['alias_value' => $text]
            );

            return response()->json(['success' => true, 'message' => "Alias creado: {$text} → {$enterprise->title}"]);
        }

        if ($type === 'course') {
            // where()->first(), no Course::id() -- ver comentario en getCourses().
            $course = Course::where('id', $targetId)->first();
            if (! $course instanceof Course) {
                return response()->json(['success' => false, 'message' => 'Curso no encontrado.']);
            }
            $matcher = new CourseMatcher;
            CourseAlias::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'normalized_alias' => $matcher->normalize($text),
                ],
                ['alias' => $text, 'source' => 'manual']
            );

            return response()->json(['success' => true, 'message' => "Alias creado: {$text} → {$course->title}"]);
        }

        return response()->json(['success' => false, 'message' => 'Tipo inválido.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $enterpriseId = $request->input('enterprise_id', '');

        $query = IncomingMail::query()
            ->with('enterprise')
            ->descending()
            ->when($search !== '', fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('from', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('parsed_payload', 'like', "%{$search}%");
            }))
            ->when($dateFrom !== '' && $dateTo !== '', fn ($q) => $q->whereBetween('received_at', [
                $dateFrom.' 00:00:00',
                $dateTo.' 23:59:59',
            ]))
            ->when($enterpriseId !== '', fn ($q) => $q->where('matched_enterprise_id', $enterpriseId));

        if ($status !== null && $status !== '') {
            $query = $query->status($status);
        }

        $filename = 'correos_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, ['ID', 'Remitente', 'Asunto', 'Empresa', 'Confianza %', 'Estado', 'Recibido', 'Procesado'], ',', '"', '\\');

            $query->chunk(250, function ($mails) use ($handle) {
                foreach ($mails as $mail) {
                    fputcsv($handle, [
                        $mail->slack,
                        $mail->from,
                        $mail->subject,
                        $mail->enterprise?->title ?? '',
                        $mail->confidence_score ?? '',
                        $mail->status,
                        $mail->received_at?->format('Y-m-d H:i:s') ?? '',
                        $mail->processed_at?->format('Y-m-d H:i:s') ?? '',
                    ], ',', '"', '\\');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $action = $request->input('action');
        $slacks = array_values(array_filter((array) $request->input('slacks', [])));

        if (empty($slacks) || ! in_array($action, ['reparse', 'discard'])) {
            return response()->json(['success' => false, 'message' => 'Parámetros inválidos.']);
        }

        $mails = IncomingMail::query()->whereIn('slack', $slacks)->get();

        if ($mails->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron correos.']);
        }

        if ($action === 'discard') {
            IncomingMail::query()
                ->whereIn('slack', $slacks)
                ->where('status', '!=', IncomingMail::STATUS_PROCESSED)
                ->update(['status' => IncomingMail::STATUS_IGNORED]);

            return response()->json(['success' => true, 'message' => "Se descartaron {$mails->count()} correos."]);
        }

        $count = 0;
        $enterpriseMatcher = new EnterpriseMatcher;
        $courseMatcher = new CourseMatcher;

        DB::transaction(function () use ($mails, $enterpriseMatcher, $courseMatcher, &$count) {
            foreach ($mails as $mail) {
                try {
                    $payload = $mail->parsed_payload ?? [];
                    $enterprise = $enterpriseMatcher->match(
                        $payload['enterprise_code'] ?? null,
                        $payload['enterprise_name'] ?? null
                    );
                    $courseMatches = array_map(
                        fn (string $txt) => $courseMatcher->match($txt, $enterprise),
                        $payload['courses'] ?? []
                    );
                    $allMatched = count($payload['courses'] ?? []) > 0
                        && ! in_array(null, $courseMatches, true);

                    $mail->matched_enterprise_id = $enterprise?->id;
                    $mail->status = ($enterprise instanceof Enterprise && $allMatched)
                        ? IncomingMail::STATUS_PENDING_REVIEW
                        : IncomingMail::STATUS_FAILED;
                    $mail->error_log = null;
                    $mail->save();
                    $count++;
                } catch (\Throwable) {
                    // continue with remaining mails
                }
            }
        });

        return response()->json(['success' => true, 'message' => "Se re-analizaron {$count} correos."]);
    }

    public function missingAliases(): JsonResponse
    {
        $since = now()->subDays(30);

        $enterpriseCodes = DB::table('incoming_mails')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(parsed_payload, '$.enterprise_code')) as code, COUNT(*) as total")
            ->whereIn('status', [IncomingMail::STATUS_PENDING_REVIEW, IncomingMail::STATUS_FAILED])
            ->whereNull('matched_enterprise_id')
            ->whereRaw("JSON_EXTRACT(parsed_payload, '$.enterprise_code') IS NOT NULL")
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(parsed_payload, '$.enterprise_code')) != ''")
            ->where('received_at', '>=', $since)
            ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(parsed_payload, '$.enterprise_code'))")
            ->orderByDesc('total')
            ->limit(25)
            ->get();

        $courseMatcher = new CourseMatcher;
        $rawCourseTexts = [];

        IncomingMail::query()
            ->whereIn('status', [IncomingMail::STATUS_PENDING_REVIEW, IncomingMail::STATUS_FAILED])
            ->where('received_at', '>=', $since)
            ->whereNotNull('parsed_payload')
            ->get(['parsed_payload'])
            ->each(function ($mail) use (&$rawCourseTexts) {
                foreach ($mail->parsed_payload['courses'] ?? [] as $text) {
                    $key = strtolower(trim((string) $text));
                    if ($key === '') {
                        return;
                    }
                    $rawCourseTexts[$key] = [
                        'text' => $text,
                        'count' => ($rawCourseTexts[$key]['count'] ?? 0) + 1,
                    ];
                }
            });

        $normalizedKeys = array_map(
            fn ($item) => $courseMatcher->normalize((string) $item['text']),
            $rawCourseTexts
        );

        $existingAliases = CourseAlias::query()
            ->whereIn('normalized_alias', $normalizedKeys)
            ->pluck('normalized_alias')
            ->flip()
            ->all();

        $missingCourses = [];
        foreach ($rawCourseTexts as $item) {
            $normalized = $courseMatcher->normalize((string) $item['text']);
            if (! isset($existingAliases[$normalized])) {
                $missingCourses[] = ['text' => $item['text'], 'count' => $item['count']];
            }
        }

        usort($missingCourses, fn ($a, $b) => $b['count'] - $a['count']);

        return response()->json([
            'enterprise_codes' => $enterpriseCodes,
            'course_texts' => array_slice($missingCourses, 0, 25),
        ]);
    }

    public function pendingCount(): JsonResponse
    {
        $count = IncomingMail::query()
            ->whereIn('status', [IncomingMail::STATUS_PENDING_REVIEW, IncomingMail::STATUS_FAILED])
            ->count();

        return response()->json(['count' => $count]);
    }

    private function persistEnterpriseAlias(IncomingMail $mail, Enterprise $enterprise, array $payload): void
    {
        $enterpriseMatcher = new EnterpriseMatcher;

        if (isset($payload['enterprise_code']) && $payload['enterprise_code'] !== '') {
            EnterpriseAlias::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'alias_type' => EnterpriseAlias::TYPE_CODE,
                    'normalized_value' => $enterpriseMatcher->normalize($payload['enterprise_code']),
                ],
                ['alias_value' => $payload['enterprise_code']]
            );
        }

        if (isset($payload['enterprise_name']) && $payload['enterprise_name'] !== '') {
            EnterpriseAlias::firstOrCreate(
                [
                    'enterprise_id' => $enterprise->id,
                    'alias_type' => EnterpriseAlias::TYPE_NAME,
                    'normalized_value' => $enterpriseMatcher->normalize($payload['enterprise_name']),
                ],
                ['alias_value' => $payload['enterprise_name']]
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

            CourseAlias::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'normalized_alias' => $courseMatcher->normalize((string) $courseText),
                ],
                [
                    'alias' => (string) $courseText,
                    'source' => 'manual',
                ]
            );
        }
    }
}
