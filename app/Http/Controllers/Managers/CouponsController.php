<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\Bundle\Bundle;
use App\Models\Coupon\Coupon;
use App\Models\Coupon\CouponUsage;
use App\Models\Course\Course;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $coupons = Coupon::descending();

        if ($searchKey != null) {
            $coupons = $coupons->where('code', 'like', '%'.$searchKey.'%');
        }

        if ($available != null) {
            $coupons = $coupons->where('available', $available);
        }

        $coupons = $coupons->paginate(paginationNumber());

        return view('managers.views.settings.coupons.index')->with([
            'coupons' => $coupons,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function create()
    {

        $courses = Course::latest()->available()->get();
        $courses = $courses->pluck('title', 'id');

        $bundles = Bundle::latest()->available()->get();
        $bundles = $bundles->pluck('title', 'id');

        $availables = $this->availableOptions();

        $types = collect([
            ['id' => '1', 'title' => 'Tasa de descuento (%)'],
            ['id' => '0', 'title' => 'Tarifa plana'],
        ]);

        $types = $types->pluck('title', 'id');

        return view('managers.views.settings.coupons.create')->with([
            'availables' => $availables,
            'courses' => $courses,
            'bundles' => $bundles,
            'types' => $types,
        ]);

    }

    public function edit($slack)
    {

        $coupon = Coupon::slack($slack);

        $courses = Course::latest()->available()->get();
        $courses = $courses->pluck('title', 'id');

        $bundles = Bundle::latest()->available()->get();
        $bundles = $bundles->pluck('title', 'id');

        $availables = $this->availableOptions();

        $types = collect([
            ['id' => '1', 'title' => 'Tasa de descuento (%)'],
            ['id' => '0', 'title' => 'Tarifa plana'],
        ]);

        $types = $types->pluck('title', 'id');

        $usageCount = CouponUsage::where('coupon_id', $coupon->id)->sum('usage_count');

        return view('managers.views.settings.coupons.edit')->with([
            'coupon' => $coupon,
            'usageCount' => $usageCount,
            'types' => $types,
            'bundles' => $bundles,
            'courses' => $courses,
            'availables' => $availables,
        ]);

    }

    public function update(Request $request)
    {

        $coupon = Coupon::slack($request->slack);
        $coupon->title = $request->title;
        $coupon->description = $request->description;
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->amount = $request->amount;
        $coupon->bundle_ids = ! empty($request->bundles) ? $request->bundles : null;
        $coupon->course_ids = ! empty($request->courses) ? $request->courses : null;
        $coupon->available = $request->available;
        $coupon->min_price = $request->min_price;
        $coupon->limit = $request->limit ?? 0;

        if ($request->date_var) {
            $date_var = explode(' - ', $request->date_var);
            if (count($date_var) == 2) {
                $coupon->start_date = date('Y-m-d', strtotime($date_var[0]));
                $coupon->end_date = date('Y-m-d', strtotime($date_var[1]));
            }
        }

        $coupon->update();

        return response()->json([
            'success' => true,
            'slack' => $coupon->slack,
            'message' => 'Se actualizó el cupón correctamente',
        ]);

    }

    public function store(Request $request)
    {

        $coupon = new Coupon;
        $coupon->slack = $this->generate_slack('coupons');
        $coupon->title = $request->title;
        $coupon->description = $request->description;
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->amount = $request->amount;
        $coupon->bundle_ids = ! empty($request->bundles) ? $request->bundles : null;
        $coupon->course_ids = ! empty($request->courses) ? $request->courses : null;
        $coupon->available = $request->available;
        $coupon->min_price = $request->min_price;
        $coupon->limit = $request->limit ?? 0;

        if ($request->date_var) {
            $date_var = explode(' - ', $request->date_var);
            if (count($date_var) == 2) {
                $coupon->start_date = date('Y-m-d', strtotime($date_var[0]));
                $coupon->end_date = date('Y-m-d', strtotime($date_var[1]));
            }
        }

        $coupon->save();

        return response()->json([
            'success' => true,
            'slack' => $coupon->slack,
            'message' => 'Se creado el cupon correctamente',
        ]);

    }

    public function destroy($slack)
    {

        $coupon = Coupon::slack($slack);
        $coupon->delete();

        return redirect()->route('manager.coupons');
    }
}
