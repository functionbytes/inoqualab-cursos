<?php

namespace App\Services\IncomingMail;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Mail\IncomingMail;
use App\Models\User;
use App\Notifications\IncomingMail\UnprocessableMailNotification;
use App\Services\Concerns\GeneratesSlackAndNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class IncomingMailProcessor
{
    use GeneratesSlackAndNumber;

    /**
     * Process a raw mail DTO and persist an IncomingMail record.
     *
     * Idempotent: returns the existing record if already processed.
     *
     * @param  array<string, mixed>  $dto  Keys: message_id, from, subject, body, received_at
     */
    public function processRaw(array $dto): IncomingMail
    {
        $existing = IncomingMail::where('message_id', $dto['message_id'])->first();

        if ($existing !== null) {
            return $existing;
        }

        $mail = new IncomingMail;
        $mail->slack = $this->generateSlack(IncomingMail::class);
        $mail->message_id = $dto['message_id'];
        $mail->from = $dto['from'];
        $mail->subject = $dto['subject'];
        $mail->received_at = Carbon::parse($dto['received_at']);
        $mail->raw_body = $dto['body'];
        $mail->status = IncomingMail::STATUS_PENDING_REVIEW;

        $trustedSenders = config('incoming_mail.trusted_senders', []);
        $s = setting('incoming_mail_trust_all_senders');
        $trustAll = $s !== '' ? $s === 'true' : (bool) config('incoming_mail.trust_all_senders', false);

        if (! $trustAll && ! in_array($mail->from, $trustedSenders, true)) {
            $mail->status = IncomingMail::STATUS_IGNORED;
            $mail->save();

            return $mail;
        }

        $parser = (new ParserResolver)->resolve($mail->from);

        // Modo pruebas: si no hay parser específico para el remitente, usar el
        // parser por defecto para poder procesar correos de cualquier origen.
        if ($parser === null && $trustAll) {
            $defaultParser = config('incoming_mail.default_parser');
            $parser = $defaultParser ? app($defaultParser) : null;
        }

        if ($parser === null) {
            $mail->status = IncomingMail::STATUS_IGNORED;
            $mail->save();

            return $mail;
        }

        $payload = $parser->parse($mail->subject, $mail->raw_body);
        $mail->parsed_payload = $payload;

        $enterprise = (new EnterpriseMatcher)->match(
            $payload['enterprise_code'] ?? null,
            $payload['enterprise_name'] ?? null
        );

        $mail->matched_enterprise_id = $enterprise?->id;

        $courseMatches = array_map(
            fn (string $txt) => (new CourseMatcher)->match($txt, $enterprise),
            $payload['courses'] ?? []
        );

        $score = (new ConfidenceScorer)->score($payload, $enterprise, $courseMatches);
        $mail->confidence_score = $score;

        $allMatched = count($payload['courses'] ?? []) > 0
            && ! in_array(null, $courseMatches, true);

        $sAuto = setting('incoming_mail_auto_process');
        $autoProcess = $sAuto !== '' ? $sAuto === 'true' : (bool) config('incoming_mail.auto_process');

        $sThreshold = setting('incoming_mail_confidence_threshold');
        $threshold = $sThreshold !== '' ? (int) $sThreshold : (int) config('incoming_mail.confidence_threshold');

        $shouldAutoProcess = $autoProcess
            && $score >= $threshold
            && $enterprise !== null
            && $allMatched;

        if ($shouldAutoProcess) {
            $this->autoProcess($mail, $payload, $enterprise, $courseMatches);
        }

        $mail->save();

        return $mail;
    }

    /**
     * @param  Enterprise  $enterprise
     * @param  (Course|null)[]  $courseMatches
     */
    private function autoProcess(
        IncomingMail $mail,
        array $payload,
        mixed $enterprise,
        array $courseMatches
    ): void {
        try {
            $order = (new OrderCreator)->createFromPayload(
                $mail,
                $payload,
                $enterprise,
                $courseMatches
            );

            if ($order !== null) {
                $mail->status = IncomingMail::STATUS_PROCESSED;
                $mail->order_id = $order->id;
            } else {
                $mail->status = IncomingMail::STATUS_PROCESSED;
                $mail->error_log = 'sin cursos nuevos';
            }

            $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
        } catch (\Throwable $e) {
            $mail->status = IncomingMail::STATUS_FAILED;
            $mail->error_log = $e->getMessage();

            $this->notifySupportTeam($mail);
        }
    }

    private function notifySupportTeam(IncomingMail $mail): void
    {
        $recipients = User::query()
            ->whereIn('role', ['support', 'manager'])
            ->where('available', 1)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new UnprocessableMailNotification($mail));
        }
    }
}
