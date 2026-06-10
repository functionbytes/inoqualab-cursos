<?php

namespace App\Http\Controllers\Enterprises\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Inscription;
use DB;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
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
        $course = Course::slack($slack);

        $inscriptions = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $enterprise->id)
            ->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })->join('orders', function ($join) {
                $join->on('orders.id', '=', 'inscriptions.order_id');
            })->where('inscriptions.course_id', '=', $course->id)->select(
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

        $years = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $enterprise->id)
            ->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })->join('orders', function ($join) {
                $join->on('orders.id', '=', 'inscriptions.order_id');
            })->where('inscriptions.course_id', '=', $course->id)
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

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('enterprises.views.enterprises.courses.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function details($slack)
    {

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
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
