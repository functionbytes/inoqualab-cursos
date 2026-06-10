<?php

namespace App\Http\Controllers\Managers\Enterprises;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Method;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnterprisesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $enterprises = Enterprise::descending();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('managers.views.enterprises.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.create')->with([
            'availables' => $availables,
        ]);

    }

    public function edit($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = $this->availableOptions();

        return view('managers.views.enterprises.enterprises.edit')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function update(Request $request)
    {
        $enterprise = Enterprise::slack($request->slack);

        if (Enterprise::where('email', $request->email)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->where('id', '!=', $enterprise->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = $request->available;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa actualizada correctamente.',
        ]);
    }

    public function store(Request $request)
    {
        if (Enterprise::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'El correo electrónico ya está registrado en nuestro sistema.']);
        }

        if (Enterprise::where('nit', $request->nit)->exists()) {
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado en nuestro sistema.']);
        }

        $enterprise = new Enterprise;
        $enterprise->slack = $this->generate_slack('enterprises');
        $enterprise->title = Str::upper($request->title);
        $enterprise->slug = Str::slug($request->title, '-');
        $enterprise->address = $request->address;
        $enterprise->cellphone = $request->cellphone;
        $enterprise->nit = $request->nit;
        $enterprise->email = $request->email;
        $enterprise->available = 1;
        $enterprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Empresa creada correctamente.',
        ]);
    }

    public function destroy($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $enterprise->delete();

        return redirect()->route('manager.enterprises');

    }

    public function navegation($slack)
    {

        $enterprise = Enterprise::slack($slack);

        return view('managers.views.enterprises.enterprises.navegation')->with([
            'enterprise' => $enterprise,
        ]);

    }

    public function inscriptions($slack)
    {

        $enterprise = Enterprise::slack($slack);
        $users = $enterprise->users()->available()->get();
        $courses = $enterprise->courses()->available()->get();
        $users = $users->pluck('identification', 'identification');
        $courses = $courses->pluck('title', 'id');

        return view('managers.views.enterprises.enterprises.inscription')->with([
            'enterprise' => $enterprise,
            'users' => $users,
            'courses' => $courses,
        ]);

    }

    public function generate(Request $request)
    {

        $enterprise = Enterprise::slack($request->enterprise);
        $course = Course::id($request->course);

        $users = explode(',', $request->users);
        $condition = InvoiceCondition::slug('pagada');
        $method = Method::slug('acuerdo');

        foreach ($users as $user) {

            $validate = User::identification($user);

            $order = new Order;
            $order->slack = $this->generate_slack('orders');
            $order->subtotal = 0;
            $order->discount = 0;
            $order->total = 0;
            $order->transaction = null;
            $order->condition_id = $condition->id;
            $order->method_id = $method->id;
            $order->course_id = $course->id;
            $order->user_id = $validate->id;
            $order->enroll_start = Carbon::now()->setTimezone('America/Bogota');
            $order->enroll_expire = Carbon::now()->setTimezone('America/Bogota')->addMonths(3);
            $order->payment_at = Carbon::now()->setTimezone('America/Bogota');

            $include = new Inscription;
            $include->user_id = $validate->id;
            $include->course_id = $course->id;
            $include->culminated = 0;
            $include->culminated_at = null;

            DB::transaction(function () use ($order, $include) {
                $order->save();
                $include->order_id = $order->id;
                $include->save();
            });

        }

        return response()->json([
            'success' => true,
            'message' => 'Se generado correctamente la empresa',
            'slack' => $enterprise->slack,
        ]);

    }
}
