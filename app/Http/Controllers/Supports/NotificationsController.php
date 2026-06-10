<?php

namespace App\Http\Controllers\Supports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()->paginate(10)->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('supports.views.notifications.index', compact('notifications'));
    }

    public function show(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }

    public function markasread(Request $request)
    {
        Auth::user()->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })->markAsRead();

        return response()->noContent();
    }

    public function allactiveinprogresstickets(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()->paginate(10)->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('supports.views.notifications.index', compact('notifications'));
    }
}
