<?php

namespace App\Http\Controllers\Managers\Users;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Models\User;

class MailLogsController extends Controller
{
    public function index($slack)
    {
        $user = User::slack($slack);

        $logs = MailLog::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('recipient_email', $user->email);
        })
            ->orderByDesc('sent_at')
            ->paginate(paginationNumber(20));

        $view = request()->ajax() ? 'managers.views.users.emails._table' : 'managers.views.users.emails.index';

        return view($view, compact('user', 'logs'));
    }

    public function show($id)
    {
        $log = MailLog::findOrFail($id);
        $user = $log->user ?? User::where('email', $log->recipient_email)->first();

        return view('managers.views.users.emails.show', compact('log', 'user'));
    }
}
