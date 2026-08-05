<?php

namespace App\Http\Controllers\Supports\Users;

use App\Exports\Distributors\IncomesExport;
use App\Exports\Supports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Supports\Concerns\ValidatesUniqueUserFields;
use App\Imports\Managers\UsersImport;
use App\Models\Course\CourseProgress;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ManagementController extends Controller
{
    use ValidatesUniqueUserFields;

    public function index(Request $request, $slack)
    {

        $inscription = Inscription::slack($slack);
        $course = $inscription->course;

        return view('supports.views.users.managements.index')->with([
            'inscription' => $inscription,
            'course' => $course,
        ]);
    }

    public function progressView($slack)
    {

        $inscription = Inscription::slack($slack);
        $progress = $inscription->progress;
        $user = $inscription->user;
        $course = $inscription->course;
        $class = $course->lessons;

        return view('supports.views.users.managements.progress')->with([
            'user' => $user,
            'course' => $course,
            'progress' => $progress,
            'class' => $class,
            'inscription' => $inscription,
        ]);

    }

    public function progressRestoreSingle($slack)
    {
        $inscription = null;
        $progress = CourseProgress::id($slack);
        $inscription = $progress->inscription;
        $progress->delete();

        return redirect()->route('support.enterprises.users.managements.progress.view', $inscription->slack);
    }

    public function progressRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        $inscription->progress()->delete(); // Elimina todos los registros relacionados

        return redirect()->route('support.enterprises.users.managements.progress.view', $inscription->slack);
    }

    public function quizView($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        $quizs = $inscription->quizs()->with('lesson')->get();

        return view('supports.views.users.managements.quizs')->with([
            'inscription' => $inscription,
            'course' => $inscription->course,
            'user' => $inscription->user,
            'quizs' => $quizs,
        ]);
    }

    public function quizRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        // Reinicia los intentos: el quiz se recrea cuando el estudiante vuelve a entrar.
        foreach ($inscription->quizs as $quiz) {
            $quiz->answers()->delete();
            $quiz->delete();
        }

        return redirect()->route('support.enterprises.users.managements.quiz.view', $inscription->slack);
    }

    public function examView($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        return view('supports.views.users.managements.exam')->with([
            'inscription' => $inscription,
            'course' => $inscription->course,
            'user' => $inscription->user,
            'exam' => $inscription->exam,
        ]);
    }

    public function examRestore($slack)
    {
        $inscription = Inscription::slack($slack);
        abort_unless($inscription instanceof Inscription, 404);

        // Mismo reinicio que el "intentar de nuevo" del aula: puntaje en cero y respuestas fuera.
        $exam = $inscription->exam;

        if ($exam) {
            $exam->update(['correct' => 0, 'wrong' => 0, 'score' => 0]);
            $exam->answers()->delete();
        }

        return redirect()->route('support.enterprises.users.managements.exam.view', $inscription->slack);
    }

    public function dashboard($slack)
    {

        $enterprise = Enterprise::slack($slack);

        return view('supports.views.enterprises.users.users.dashboard')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function create($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.users.users.create')->with([
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);
    }

    public function view($slack)
    {

        $user = User::slack($slack);
        $enterprise = $user->relations;

        return view('supports.views.enterprises.users.users.view')->with([
            'user' => $user,
            'enterprise' => $enterprise,
        ]);
    }

    public function edit($slack)
    {

        $user = User::slack($slack);
        $enterprise = $user->relations;

        $availables = collect([
            ['id' => '1', 'label' => 'Activo'],
            ['id' => '0', 'label' => 'Inactivo'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('supports.views.enterprises.users.users.edit')->with([
            'user' => $user,
            'enterprise' => $enterprise,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {
        $user = User::slack($request->slack);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification, $user)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        $user->firstname = Str::upper($request->firstname);
        $user->lastname = Str::upper($request->lastname);
        $user->identification = $request->identification;
        $user->cellphone = $request->cellphone;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->available = $request->available;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Se ha actualizado correctamente el perfil',
        ]);
    }

    public function store(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);

        if ($error = $this->uniqueUserFieldError($request->email, $request->identification)) {
            return response()->json(['success' => false, 'message' => $error]);
        }

        DB::transaction(function () use ($request, $enterprise) {
            $user = new User;
            $user->slack = $this->generate_slack('users');
            $user->firstname = Str::upper($request->firstname);
            $user->lastname = Str::upper($request->lastname);
            $user->cellphone = $request->cellphone;
            $user->identification = $request->identification;
            $user->email = $request->email;
            $user->address = $request->address;
            $request->filled('password') ? $user->password = $request->password : $user->password = $request->identification;
            $user->role = 'customer';
            $user->available = 1;
            $user->terms = 1;
            $user->page = 0;
            $user->setting = 0;
            $user->validation = 1;
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseUser;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Se ha creado correctamente ',
        ]);

    }

    public function users($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $users = $enterprise->users()->with(['certificates', 'inscriptions'])->get();

        return view('supports.views.enterprises.users.users.index')->with([
            'enterprise' => $enterprise,
            'users' => $users,
        ]);
    }

    public function courses($slack)
    {

        $user = User::slack($slack);
        $inscriptions = $user->inscriptions()->with('course');
        $inscriptions = $inscriptions->paginate(paginationNumber());

        return view('supports.views.enterprises.users.courses.index')->with([
            'user' => $user,
            'inscriptions' => $inscriptions,
        ]);
    }

    public function report($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $modalities = collect([
            ['id' => '0', 'title' => 'Todos'],
            ['id' => '1', 'title' => 'Publico'],
            ['id' => '2', 'title' => 'Inactivos'],
        ]);

        $modalities = $modalities->pluck('title', 'id');

        return view('supports.views.enterprises.users.users.report')->with([
            'modalities' => $modalities,
            'enterprise' => $enterprise,
        ]);
    }

    public function income($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $courses = $enterprise->courses;
        $courses = $courses->pluck('title', 'id');

        return view('supports.views.enterprises.users.users.income')->with([
            'courses' => $courses,
            'enterprise' => $enterprise,
        ]);
    }

    public function import($slack)
    {

        $enterprise = Enterprise::slack($slack);

        return view('supports.views.enterprises.users.users.import')->with([
            'enterprise' => $enterprise,
        ]);
    }

    public function importation(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'Selecciona un archivo para importar.',
            'file.mimes' => 'El archivo debe ser .xlsx, .xls o .csv.',
            'file.max' => 'El archivo no debe superar 5 MB.',
        ]);

        $enterprise = Enterprise::slack($request->enterprise);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {

            try {
                Excel::import(new UsersImport($enterprise->slack), request()->file('file'));
            } catch (ValidationException $e) {

                $failures = $e->failures();

                return view('supports.views.enterprises.users.users.response')->with([
                    'error_message' => $e->getMessage(),
                    'failures' => $failures,
                    'enterprise' => $enterprise,
                ]);
            }
        }

        // Bug: 'support.supports.users' no existe como ruta -- RouteNotFoundException
        // garantizada al importar correctamente.
        return redirect()->route('support.enterprises.users', $enterprise->slack);
    }

    public function generate(Request $request)
    {

        $modalitie = $request->modalitie;
        $enterprise = $request->enterprise;

        return Excel::download(new UsersExport($enterprise, $modalitie), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function incoming(Request $request)
    {

        $course = $request->course;
        $enterprise = $request->enterprise;
        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(new IncomesExport($enterprise, $course, $start, $end), 'REPORTE USUARIOS '.date('Y-m-d').'.xlsx');
    }

    public function check(Request $request)
    {
        $user = User::where('identification', $request->identification)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario no está registrado.',
            ]);
        }

        $enterpriseUser = EnterpriseUser::where('user_id', $user->id)->first();

        if (! $enterpriseUser) {
            return response()->json([
                'success' => true,
                'message' => 'Este usuario ya está registrado pero no está asignado a ninguna empresa.',
                'enterprise' => null,
            ]);
        }

        // Registro huérfano (enterprise_id sin fila en enterprises): antes
        // dejaba caer el método sin ningún return (sin el "else" de este if).
        $enterprise = Enterprise::find($enterpriseUser->enterprise_id);

        if (! $enterprise) {
            return response()->json([
                'success' => true,
                'message' => 'Este usuario ya está registrado pero su empresa asignada ya no existe.',
                'enterprise' => null,
            ]);
        }

        $distributorEnterprise = DistributorEnterprise::where('enterprise_id', $enterprise->id)->first();

        if (! $distributorEnterprise) {
            return response()->json([
                'success' => true,
                'message' => 'Este usuario ya está registrado en la empresa: '.$enterprise->title.', pero no está asignado a ningún distribuidor.',
                'enterprise' => $enterprise->title,
                'distributor' => null,
            ]);
        }

        // Mismo huérfano posible en distributor_id: sin esto, ->title sobre null.
        $distributor = Distributor::find($distributorEnterprise->distributor_id);

        if (! $distributor) {
            return response()->json([
                'success' => true,
                'message' => 'Este usuario ya está registrado en la empresa: '.$enterprise->title.', pero su distribuidor asignado ya no existe.',
                'enterprise' => $enterprise->title,
                'distributor' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Este usuario ya está registrado y asignado a la empresa: '.$enterprise->title,
            'enterprise' => $enterprise->title,
            'distributor' => $distributor->title,
            // Bug: 'distributor.supports.users' no existe como ruta -- RouteNotFoundException
            // garantizada al llegar aquí.
            'url' => route('support.enterprises.users', ['slack' => $enterprise->slack]),
        ]);
    }
}
