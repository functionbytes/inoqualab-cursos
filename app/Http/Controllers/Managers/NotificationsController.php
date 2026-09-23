<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\BulkActionNotificationRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()->latest()->take(50)->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('managers.views.notifications.index', compact('notifications'));
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

    public function bulkAction(BulkActionNotificationRequest $request)
    {
        $notifications = Auth::user()->notifications()->whereIn('id', $request->ids)->get();
        $count = $notifications->count();

        match ($request->action) {
            'read' => $notifications->markAsRead(),
            'delete' => $notifications->each->delete(),
        };

        return response()->json(['success' => true, 'message' => $count.' notificación(es) procesadas.']);
    }
}
