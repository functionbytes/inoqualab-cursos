<?php

namespace App\Console\Commands\Orders;

use App\Mail\IncomingMails\MailProcessedNotificationMail;
use App\Models\Enterprise\Enterprise;
use App\Models\Mail\IncomingMail;
use App\Models\Mail\MailAutoConfirmRule;
use App\Services\IncomingMail\CourseMatcher;
use App\Services\IncomingMail\EnterpriseMatcher;
use App\Services\IncomingMail\OrderCreator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReparseFailedMails extends Command
{
    protected $signature = 'mails:reparse-failed {--limit=50 : Máximo de correos a procesar}';

    protected $description = 'Re-analiza correos fallidos y los pasa a pending_review si ahora tienen match completo';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $mails = IncomingMail::query()
            ->where('status', IncomingMail::STATUS_FAILED)
            ->whereNotNull('parsed_payload')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($mails->isEmpty()) {
            $this->info('No hay correos fallidos para re-analizar.');

            return self::SUCCESS;
        }

        $this->info("Re-analizando {$mails->count()} correos fallidos...");

        $enterpriseMatcher = new EnterpriseMatcher;
        $courseMatcher = new CourseMatcher;
        $promoted = 0;
        $autoConfirmed = 0;

        foreach ($mails as $mail) {
            try {
                $payload = $mail->parsed_payload ?? [];

                $enterprise = $enterpriseMatcher->match(
                    $payload['enterprise_code'] ?? null,
                    $payload['enterprise_name'] ?? null
                );

                $courseMatches = array_map(
                    fn (string $txt) => $courseMatcher->match($txt, $enterprise),
                    $payload['courses'] ?? []
                );

                $allMatched = count($payload['courses'] ?? []) > 0
                    && ! in_array(null, $courseMatches, true);

                $mail->matched_enterprise_id = $enterprise?->id;

                if (! ($enterprise instanceof Enterprise) || ! $allMatched) {
                    $mail->save();

                    continue;
                }

                // Check auto-confirm rule
                $rule = MailAutoConfirmRule::query()
                    ->where('enterprise_id', $enterprise->id)
                    ->where('is_active', true)
                    ->first();

                if ($rule instanceof MailAutoConfirmRule && $mail->confidence_score >= $rule->min_confidence) {
                    $courseIds = array_values(array_filter(array_map(fn ($m) => $m?->id, $courseMatches)));
                    $courses = $enterprise->courses()->whereIn('courses.id', $courseIds)->get();

                    $order = (new OrderCreator)->createFromPayload($mail, $payload, $enterprise, $courses->all(), null);

                    DB::transaction(function () use ($mail, $order) {
                        $mail->status = IncomingMail::STATUS_PROCESSED;
                        $mail->order_id = $order?->id;
                        $mail->processed_at = Carbon::now()->setTimezone('America/Bogota');
                        $mail->save();
                    });

                    if ($order !== null) {
                        try {
                            $mail->load('enterprise', 'order');
                            Mail::queue(new MailProcessedNotificationMail($mail));
                        } catch (\Throwable) {
                            // Silently ignore mail send failures
                        }
                    }

                    $autoConfirmed++;
                    $this->line("  ✓ Auto-confirmado: [{$mail->slack}] {$mail->subject}");
                } else {
                    $mail->status = IncomingMail::STATUS_PENDING_REVIEW;
                    $mail->error_log = null;
                    $mail->save();
                    $promoted++;
                    $this->line("  → Promovido a revisión: [{$mail->slack}] {$mail->subject}");
                }
            } catch (\Throwable $e) {
                Log::warning("mails:reparse-failed — error en [{$mail->slack}]: {$e->getMessage()}");
                $this->warn("  ✗ Error [{$mail->slack}]: {$e->getMessage()}");
            }
        }

        $this->info("Completado: {$promoted} promovidos, {$autoConfirmed} auto-confirmados.");

        return self::SUCCESS;
    }
}
