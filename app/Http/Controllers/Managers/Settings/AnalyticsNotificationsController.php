<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateAnalyticsNotificationsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnalyticsNotificationsController extends Controller
{
    public function index(): View
    {
        $sentEmails = json_decode(setting('analytics_notifications_sent_emails', '[]'), true) ?: [];
        $failedEmails = json_decode(setting('analytics_notifications_failed_emails', '[]'), true) ?: [];

        return view('managers.views.settings.analytics.notifications', compact('sentEmails', 'failedEmails'));
    }

    public function update(UpdateAnalyticsNotificationsRequest $request): RedirectResponse
    {
        $sentEmails = array_values(array_filter($request->input('sent_emails', []), fn ($e) => filled($e)));
        $failedEmails = array_values(array_filter($request->input('failed_emails', []), fn ($e) => filled($e)));

        updateSettings([
            'analytics_notifications_sent_emails' => json_encode($sentEmails),
            'analytics_notifications_failed_emails' => json_encode($failedEmails),
        ]);

        return redirect()
            ->route('manager.settings.analytics.notifications')
            ->with('success', 'Notificaciones guardadas correctamente.');
    }
}
