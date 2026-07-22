<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {

        $user = app('customer');

        // groupBy() sobre un paginator descarta el paginador y devuelve una Collection:
        // la vista no usa ->links(), así que la paginación no navegaba y solo se veían
        // 10 notificaciones. Traemos las 50 más recientes (notifications() ya ordena desc).
        $notifications = $user->notifications()->take(50)->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('customers.views.chats.index')->with([
            'notifications' => $notifications,
        ]);

    }

    public function view($id)
    {

        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();

        // Abrir una notificación la marca como leída (antes quedaba como no leída
        // aunque el usuario ya la hubiera visto, inflando el contador del badge).
        $notification->markAsRead();

        return view('customers.views.livechat.view')->with([
            'notification' => $notification,
        ]);

    }

    public function mark(Request $request)
    {

        Auth::user()->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })->markAsRead();

        return response()->noContent();

    }

    public function search(Request $request)
    {

        $status = $request->search;

        // El include customers.includes.notification AGRUPA por su cuenta ($notifyItems):
        // aquí se pasa la colección PLANA; agrupar antes provocaba un doble groupBy.
        if ($status) {
            $notifications = Auth::user()->notifications()->where(function ($query) use ($status) {
                $query->where('data->title', 'LIKE', "%{$status}%")
                    ->orWhere('data->ticket_id', 'LIKE', "%{$status}%")
                    ->orWhere('data->mailsubject', 'LIKE', "%{$status}%")
                    ->orWhere('data->mailtext', 'LIKE', "%{$status}%");
            })->get();
        } else {
            $notifications = Auth::user()->notifications()->take(50)->get();
        }

        $view = view('customers.includes.notification')->with([
            'notifications' => $notifications,
        ])->render();

        return response()->json(['html' => $view]);

    }

    public function delete(Request $request)
    {

        $notification = Auth::user()->notifications()->find($request->id);

        if (! $notification) {
            return response()->json(['error' => 'Notificación no encontrada.'], 404);
        }

        $notification->delete();

        return response()->json(['success' => 'Borrado exitosamente', 200]);
    }
}
