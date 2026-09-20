<?php

namespace App\Http\Controllers\Managers;

use App\Exports\Managers\CoursesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function generate(Request $request)
    {

        $enterprise = $request->enterprise;
        $course = $request->course;
        $modalitie = $request->modalitie;

        return Excel::download(new CoursesExport($course, $enterprise, $modalitie), 'Reporte cou.xlsx');

    }
}
