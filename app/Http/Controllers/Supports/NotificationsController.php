<?php

namespace App\Http\Controllers\Supports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // groupBy() sobre un paginator descarta el paginador (la vista no usa ->links()),
        // por lo que solo se veían 10 notificaciones. Traemos las 50 más recientes.
        $notifications = $user->notifications()->take(50)->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('supports.views.notifications.index', compact('notifications'));
    }

    public function show(Request $request): RedirectResponse
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

        // groupBy() sobre un paginator descarta el paginador (la vista no usa ->links()),
        // por lo que solo se veían 10 notificaciones. Traemos las 50 más recientes.
        $notifications = $user->notifications()->take(50)->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('supports.views.notifications.index', compact('notifications'));
    }
}
