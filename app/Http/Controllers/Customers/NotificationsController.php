<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {

        $user = app('customer');
        $searchKey = $request->search;

        $query = $user->notifications();

        // title/message son las claves que de verdad usa la vista (y el único
        // tipo que se genera hoy, UnprocessableMailNotification) -- buscar
        // por ticket_id/mailsubject/mailtext (formato viejo, de un dropdown
        // que ya no se usa en ninguna vista) no encontraría nada visible.
        if ($searchKey) {
            $query->where(function ($q) use ($searchKey) {
                $q->where('data->title', 'LIKE', "%{$searchKey}%")
                    ->orWhere('data->message', 'LIKE', "%{$searchKey}%");
            });
        }

        // groupBy() sobre un paginator descarta el paginador y devuelve una Collection:
        // la vista no usa ->links(), así que la paginación no navegaba y solo se veían
        // 10 notificaciones. Traemos las 50 más recientes (notifications() ya ordena desc).
        $notifications = $query->take(50)->get()->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        $variant = portalVariant('customers_notifications_variant');

        // El buscador se resuelve por AJAX (ver el script en chats/index.blade.php):
        // se devuelve solo el fragmento re-renderizado en vez de la página completa.
        if ($request->ajax()) {
            $planas = $notifications->flatten(1);

            return response()->json([
                'html' => view('customers.partials.views.chats.list', compact('notifications', 'searchKey'))->render(),
                'total' => $planas->count(),
                // Str::plural('notificación', ...) usa reglas de pluralización en
                // inglés y da "notificacións" -- a mano, como corresponde en español.
                'label' => $planas->count() === 1 ? 'notificación' : 'notificaciones',
            ]);
        }

        return view('customers.views.chats.index'.$variant)->with([
            'notifications' => $notifications,
            'searchKey' => $searchKey,
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
