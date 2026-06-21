<?php

namespace App\Mail\Analytics;

use App\Models\AnalyticsReportSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnalyticsReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly AnalyticsReportSchedule $schedule,
        public readonly string $reportPath,
        public readonly array $summary = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte Analytics: '.$this->schedule->name.' — '.now()->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mailers.analytics.report',
        );
    }

    public function attachments(): array
    {
        $extension = pathinfo($this->reportPath, PATHINFO_EXTENSION);

        return [
            Attachment::fromPath($this->reportPath)
                ->as('reporte-analytics-'.$this->schedule->name.'-'.now()->format('Y-m-d').'.'.$extension)
                ->withMime($this->getMimeForExtension($extension)),
        ];
    }

    private function getMimeForExtension(string $extension): string
    {
        return match ($extension) {
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
