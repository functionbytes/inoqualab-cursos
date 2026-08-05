<?php

namespace App\Http\Controllers\Enterprises\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    /** Inscripción cuyo usuario pertenece a la empresa autenticada, o 404 (evita IDOR). */
    private function managedInscription(string $slack): Inscription
    {
        $inscription = Inscription::slack($slack);
        abort_unless(app('enterprise')->users()->where('users.id', $inscription->user_id)->exists(), 404);

        return $inscription;
    }

    public function index(Request $request)
    {

        $searchKey = $request->search;
        $enterprise = app('enterprise');
        $courses = $enterprise->courses()->with(['categorie']);

        if ($searchKey != null) {
            $courses = $courses->where('title', 'like', '%'.$searchKey.'%');
        }

        $courses = $courses->paginate(paginationNumber());

        return view('enterprises.views.enterprises.courses.index')->with([
            'enterprise' => $enterprise,
            'courses' => $courses,
            'searchKey' => $searchKey,
        ]);

    }

    public function view(Request $request, $slack)
    {

        $searchKey = $request->search;
        $year = $request->year;
        $culminated = $request->culminated;
        $enterprise = app('enterprise');
        // Ownership: el curso debe estar asignado a la empresa autenticada
        // (evita ver/enumerar metadata de cursos ajenos o no publicados).
        $course = $enterprise->courses()->where('courses.slack', $slack)->firstOrFail();

        $baseQuery = fn () => User::query()
            ->join('enterprise_user', fn ($j) => $j->on('users.id', '=', 'enterprise_user.user_id'))
            ->where('enterprise_user.enterprise_id', $enterprise->id)
            ->join('inscriptions', fn ($j) => $j->on('users.id', '=', 'inscriptions.user_id'))
            ->join('orders', fn ($j) => $j->on('orders.id', '=', 'inscriptions.order_id'))
            ->where('inscriptions.course_id', $course->id);

        $inscriptions = $baseQuery()->select(
            'users.slack',
            'users.firstname',
            'users.lastname',
            'users.available',
            'users.identification',
            'inscriptions.id',
            'inscriptions.slack as slack',
            'inscriptions.percent',
            'inscriptions.order_id',
            'inscriptions.enroll_start',
            'inscriptions.enroll_expire',
            'inscriptions.enroll_culminated',
            'inscriptions.culminated',
            'inscriptions.created_at',
            'inscriptions.updated_at',
        )->orderBy('enroll_culminated', 'desc');

        $years = $baseQuery()
            ->selectRaw('YEAR(enroll_culminated) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($searchKey) {
            $inscriptions = $inscriptions->where(function ($query) use ($searchKey) {
                $query->where('users.firstname', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.lastname', 'like', '%'.$searchKey.'%')
                    ->orWhere(DB::raw("CONCAT(users.firstname, ' ', users.lastname)"), 'like', '%'.$searchKey.'%')
                    ->orWhere('users.email', 'like', '%'.$searchKey.'%')
                    ->orWhere('users.identification', 'like', '%'.$searchKey.'%');
            });
        }

        if ($year != null) {
            $inscriptions = $inscriptions->whereYear('inscriptions.enroll_culminated', $year);
        }

        if ($culminated != null) {
            $inscriptions = $inscriptions->where('inscriptions.culminated', $culminated);
        }

        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('enterprises.views.enterprises.courses.view')->with([
            'course' => $course,
            'culminated' => $culminated,
            'inscriptions' => $inscriptions,
            'count' => $inscriptions,
            'enterprise' => $enterprise,
            'year' => $year,
            'years' => $years,
            'searchKey' => $searchKey,
        ]);

    }

    public function progress($slack)
    {

        $inscription = $this->managedInscription($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        // El curso puede haberse borrado (soft delete) después de la inscripción.
        abort_unless($course instanceof Course, 404, 'El curso de esta inscripción ya no existe.');
        $class = $course->lessons()->with('chapter')->get();
        // Set de lecciones ya culminadas, para no consultar CourseProgress::validate() por cada fila.
        $completedLessons = $progress->where('culminated', 1)->pluck('lesson_id')->filter()->flip();

        return view('enterprises.views.enterprises.courses.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
            'completedLessons' => $completedLessons,
        ]);

    }

    public function details($slack)
    {

        $inscription = $this->managedInscription($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        // El curso puede haberse borrado (soft delete) después de la inscripción.
        abort_unless($course instanceof Course, 404, 'El curso de esta inscripción ya no existe.');
        $class = $course->lessons;

        return view('enterprises.views.enterprises.courses.details')->with([
            'user' => $user,
            'course' => $course,
            'class' => $class,
            'inscription' => $inscription,
            'progress' => $progress,
        ]);

    }
}
