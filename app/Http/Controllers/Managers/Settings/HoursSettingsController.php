<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateHoursSettingsRequest;
use App\Models\Setting\Hour;

class HoursSettingsController extends Controller
{
    public function index()
    {
        $hours = Hour::whereIn('no_id', range(1, 7))->get()->keyBy('no_id');

        $data = [];
        foreach (range(1, 7) as $i) {
            $data["bussiness{$i}"] = $hours->get($i);
        }

        return view('managers.views.settings.hours.setting')->with($data);
    }

    public function update(UpdateHoursSettingsRequest $request)
    {
        abort_unless(auth()->user()->can('settings.update'), 403);
        $anyChanged = false;
        for ($i = 1; $i <= 7; $i++) {
            if ($request->input("starttime{$i}") != $request->input("endtime{$i}")) {
                $anyChanged = true;
                break;
            }
        }

        if ($anyChanged) {
            for ($i = 1; $i <= 7; $i++) {
                $noId = $request->input("bussinessid{$i}");
                Hour::updateOrCreate(
                    ['no_id' => $noId],
                    [
                        'no_id' => $noId,
                        'weeks' => $request->input("bussiness{$i}"),
                        'starttime' => $request->input("starttime{$i}"),
                        'endtime' => $request->input("endtime{$i}"),
                        'status' => $request->input("status{$i}"),
                    ]
                );
            }
        }

        $data['hoursswitch'] = $request->has('hoursswitch') ? 'true' : 'false';
        $data['hourstitle'] = $request->hourstitle;
        $data['hourssubtitle'] = $request->hourssubtitle;
        updateSettings($data);

        return response()->json([
            'success' => true,
            'message' => 'Se actualizo correctamente el horario de soporte',
        ]);
    }
}
