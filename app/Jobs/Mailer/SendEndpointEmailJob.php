<?php

namespace App\Jobs\Mailer;

use App\Enums\EndpointLogStatus;
use App\Models\Mailer\MailerEndpoint;
use App\Models\Mailer\MailerEndpointLog;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEndpointEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [60, 120, 300];

    private const PAYLOAD_MAX_BYTES = 262144;

    private const SUBJECT_MAX_LENGTH = 255;

    private const BODY_MAX_BYTES = 5242880;

    public function __construct(
        private readonly int $endpointId,
        private readonly array $payload,
        private readonly int $logId
    ) {
        $this->onQueue(config('mailer-module.queue', 'emails'));
    }

    public function handle(): void
    {
        $log = MailerEndpointLog::find($this->logId);
        $endpoint = MailerEndpoint::find($this->endpointId);

        if (! $endpoint || ! $endpoint->is_active) {
            if ($log) {
                $log->update(['status' => EndpointLogStatus::Failed, 'error_message' => 'Endpoint not found or inactive']);
            }

            return;
        }

        if (! $endpoint->template) {
            if ($log) {
                $log->update(['status' => EndpointLogStatus::Failed, 'error_message' => 'No template configured for this endpoint']);
            }

            return;
        }

        $recipientEmail = $this->payload['email'] ?? $this->payload['recipient_email'] ?? null;

        if (! $recipientEmail || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            if ($log) {
                $log->update(['status' => EndpointLogStatus::Failed, 'error_message' => 'Invalid or missing recipient email']);
            }

            return;
        }

        try {
            $variables = $this->mapVariables($this->payload, $endpoint);
            $html = MailerTemplateRendererService::renderEmailTemplate($endpoint->template, $variables);

            if (strlen($html) > self::BODY_MAX_BYTES) {
                throw new \RuntimeException('Rendered HTML exceeds maximum size limit');
            }

            $subject = MailerTemplateRendererService::replaceVariables(
                $endpoint->template->subject ?? 'Sin asunto',
                $variables
            );
            $subject = substr($subject, 0, self::SUBJECT_MAX_LENGTH);

            $plainText = MailerTemplateRendererService::htmlToPlainText($html);

            Mail::html($html, function ($message) use ($recipientEmail, $subject, $plainText) {
                $message->to($recipientEmail)->subject($subject)->text($plainText);
            });

            if ($log) {
                $log->update([
                    'status' => EndpointLogStatus::Success,
                    'recipient_email' => $recipientEmail,
                    'mailer_subject' => $subject,
                    'sent_at' => now(),
                    'job_id' => $this->job?->getJobId(),
                ]);
            }

            $endpoint->increment('requests_count');
            $endpoint->update(['last_request_at' => now()]);
        } catch (\Exception $e) {
            Log::error('SendEndpointEmailJob failed', [
                'endpoint_id' => $this->endpointId,
                'error' => $e->getMessage(),
            ]);

            if ($log) {
                $log->update([
                    'status' => EndpointLogStatus::Failed,
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendEndpointEmailJob permanently failed', [
            'endpoint_id' => $this->endpointId,
            'log_id' => $this->logId,
            'error' => $exception->getMessage(),
        ]);

        $log = MailerEndpointLog::find($this->logId);
        if ($log && $log->status !== EndpointLogStatus::Success) {
            $log->update([
                'status' => EndpointLogStatus::Failed,
                'error_message' => 'Job failed after all retries: '.$exception->getMessage(),
            ]);
        }
    }

    private function mapVariables(array $payload, MailerEndpoint $endpoint): array
    {
        $mappings = $endpoint->variable_mappings ?? [];
        $flat = $this->flattenArray($payload);
        $variables = [];

        if (! empty($mappings)) {
            foreach ($mappings as $payloadKey => $templateVar) {
                if (isset($flat[$payloadKey])) {
                    $variables[$templateVar] = htmlspecialchars((string) $flat[$payloadKey], ENT_QUOTES, 'UTF-8');
                }
            }
        } else {
            foreach ($flat as $key => $value) {
                if (! is_array($value)) {
                    $variables[strtoupper($key)] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        return $variables;
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
