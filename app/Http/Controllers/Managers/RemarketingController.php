<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\NewsletterList;
use App\Models\RemarketingRun;
use Illuminate\View\View;

class RemarketingController extends Controller
{
    /**
     * Metadatos de las automatizaciones de remarketing: comando, descripción,
     * horario (según routes/console.php) y la lista dinámica que alimentan.
     */
    private const AUTOMATIONS = [
        [
            'command' => 'courses:notify-completed',
            'label' => 'Cross-sell post-completación',
            'description' => 'Recomienda nuevos cursos a quien completó un curso.',
            'schedule' => 'Diario 09:30',
            'list_trigger' => 'course_completed',
        ],
        [
            'command' => 'certificates:notify-expiring',
            'label' => 'Certificado por vencer',
            'description' => 'Invita a renovar el curso cuyo certificado está por vencer.',
            'schedule' => 'Diario 08:00 y 08:05 (30 y 7 días)',
            'list_trigger' => 'certificate_expiring',
        ],
        [
            'command' => 'courses:notify-expiring-access',
            'label' => 'Acceso al curso por vencer',
            'description' => 'Recuerda renovar el acceso antes de que caduque (sin completar).',
            'schedule' => 'Diario 09:15 (7 días)',
            'list_trigger' => 'course_access_expiring',
        ],
    ];

    public function index(): View
    {
        $listSizes = NewsletterList::query()
            ->withCount('subscribers')
            ->pluck('subscribers_count', 'trigger');

        $automations = collect(self::AUTOMATIONS)->map(function ($item) use ($listSizes) {
            $lastRun = RemarketingRun::where('command', $item['command'])
                ->latest('created_at')
                ->first();

            $aggregate = RemarketingRun::where('command', $item['command'])
                ->selectRaw('COUNT(*) runs, COALESCE(SUM(sent),0) total_sent')
                ->first();

            return [
                ...$item,
                'list_size' => (int) ($listSizes[$item['list_trigger']] ?? 0),
                'last_run' => $lastRun,
                'total_runs' => (int) ($aggregate->runs ?? 0),
                'total_sent' => (int) ($aggregate->total_sent ?? 0),
            ];
        });

        $recentRuns = RemarketingRun::latest('created_at')->limit(20)->get();

        return view('managers.views.newsletter.remarketing.index', compact('automations', 'recentRuns'));
    }
}
