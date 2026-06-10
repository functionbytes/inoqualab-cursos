<?php

namespace App\Http\Controllers\Distributors\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report($slack)
    {

        $user = User::auth();
        $enterprise = $user->enterprise;
        $course = Course::slack($slack);

        $listmodalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Culminado'],
            ['id' => '2', 'title' => 'Pendiente'],
        ]);

        $listmodalities = $listmodalities->pluck('title', 'id');

        return view('distributors.views.enterprises.report.index')->with([
            'listmodalities' => $listmodalities,
            'enterprises' => $enterprise,
            'course' => $course,
            'user' => $user,
        ]);
    }

    public function generate(Request $request)
    {

        $enterprise = $request->enterprise;
        $course = $request->course;
        $modalitie = $request->modalitie;

        return Excel::download(new CoursesExport($course, $enterprise, $modalitie), 'Reporte Curso.xlsx');

    }
}
