<?php

namespace App\Http\Controllers\Accountings;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

        // Stats con una sola query de agregación (sobre el total real, no solo
        // las 50 traídas arriba para la vista). La relación notifications()
        // trae un ->orderBy('created_at') heredado del trait Notifiable —
        // mezclar eso con funciones de agregación sin GROUP BY revienta en
        // MySQL (error 1140), por eso ->toBase()->reorder() antes de agregar.
        $agg = $user->notifications()->toBase()->reorder()->selectRaw(
            'COUNT(*) total,
             SUM(read_at IS NULL) unread'
        )->first();

        $stats = [
            'total' => (int) ($agg->total ?? 0),
            'unread' => (int) ($agg->unread ?? 0),
        ];

        return view('accountings.views.notifications.index', compact('notifications', 'stats'));
    }

    public function markasread(Request $request)
    {
        Auth::user()->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })->markAsRead();

        return response()->noContent();
    }
}
