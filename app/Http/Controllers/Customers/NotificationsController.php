<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {

        $user = app('customer');

        $notifications = $user->notifications()->paginate(10)->groupBy(function ($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return view('customers.views.chats.index')->with([
            'notifications' => $notifications,
        ]);

    }

    public function view($id)
    {

        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();

        return view('customers.views.livechat.view')->with([
            'notification' => $notification,
        ]);

    }

    public function status(Request $request)
    {

        $status = $request->statusnotify;

        if (! $status) {
            $notifications = Auth::user()->notifications()->paginate(10)->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
        } else {
            $notifications = Auth::user()->notifications()->whereIn('data->status', $status)->paginate(10)->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
        }

        $view = view('customers.includes.notifications')->with([
            'notifications' => $notifications,
        ])->render();

        return response()->json(['html' => $view]);

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

        if ($status) {
            $notifications = Auth::user()->notifications()->where(function ($query) use ($status) {
                $query->where('data->title', 'LIKE', "%{$status}%")
                    ->orWhere('data->ticket_id', 'LIKE', "%{$status}%")
                    ->orWhere('data->mailsubject', 'LIKE', "%{$status}%")
                    ->orWhere('data->mailtext', 'LIKE', "%{$status}%");
            })->get()->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
        } else {
            $notifications = Auth::user()->notifications()->paginate()->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });
        }

        $view = view('customers.includes.notifications')->with([
            'notifications' => $notifications,
        ])->render();

        return response()->json(['html' => $view]);

    }

    public function delete(Request $request)
    {

        $notification = Auth::user()->notifications()->find($request->id);
        $notification->delete();

        return response()->json(['success' => 'Borrado exitosamente', 200]);
    }
}
