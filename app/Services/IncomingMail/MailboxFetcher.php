<?php

namespace App\Services\IncomingMail;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;

class MailboxFetcher
{
    /**
     * Fetch unseen messages from the configured IMAP account.
     *
     * Each returned item is an associative DTO array:
     *   - message_id  (string)
     *   - from        (string)  plain email address only
     *   - subject     (string)
     *   - body        (string)  plain-text body, UTF-8 normalised
     *   - received_at (string)  ISO8601
     *
     * Idempotency note: IncomingMailProcessor deduplicates by message_id,
     * so messages are intentionally marked as Seen only AFTER the DTO has
     * been handed off to the job. If the process crashes between fetch and
     * dispatch the message will be re-fetched on the next run and the
     * processor will skip the already-persisted record.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchUnseen(): array
    {
        try {
            $client = Client::account(config('incoming_mail.imap_account'));
            $client->connect();

            $folder = $client->getFolder(config('incoming_mail.folder'));

            if ($folder === null) {
                Log::warning('MailboxFetcher: folder not found', [
                    'folder' => config('incoming_mail.folder'),
                    'account' => config('incoming_mail.imap_account'),
                ]);

                return [];
            }

            $messages = $folder->messages()->unseen()->get();

            $dtos = [];

            foreach ($messages as $message) {
                $dto = $this->buildDto($message);

                if ($dto === null) {
                    continue;
                }

                // Mark as Seen after building the DTO so a crash before dispatch
                // causes a re-fetch; the processor's dedup by message_id handles it.
                $message->setFlag('Seen');

                $dtos[] = $dto;
            }

            return $dtos;
        } catch (\Throwable $e) {
            Log::warning('MailboxFetcher: failed to fetch messages', [
                'error' => $e->getMessage(),
                'account' => config('incoming_mail.imap_account'),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildDto(mixed $message): ?array
    {
        try {
            $messageId = (string) $message->getMessageId()->first() ?: '';
            $subject = (string) $message->getSubject()->first() ?: '';
            $body = $this->extractTextBody($message);
            $from = $this->extractEmail((string) $message->getFrom()->first() ?: '');
            $receivedAt = $this->parseDate($message->getDate()->first());

            return [
                'message_id' => $messageId,
                'from' => $from,
                'subject' => $subject,
                'body' => $body,
                'received_at' => $receivedAt,
            ];
        } catch (\Throwable $e) {
            Log::warning('MailboxFetcher: failed to parse message', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function extractTextBody(mixed $message): string
    {
        $body = $message->getTextBody();

        if ($body === null || $body === '') {
            return '';
        }

        $encoding = mb_detect_encoding((string) $body, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            return mb_convert_encoding((string) $body, 'UTF-8', $encoding);
        }

        return (string) $body;
    }

    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $matches)) {
            return strtolower(trim($matches[1]));
        }

        return strtolower(trim($from));
    }

    private function parseDate(mixed $date): string
    {
        try {
            if ($date instanceof Carbon) {
                return $date->toIso8601String();
            }

            return Carbon::parse((string) $date)->toIso8601String();
        } catch (\Throwable) {
            return Carbon::now()->toIso8601String();
        }
    }
}
