<?php

namespace App\Services\IncomingMail;

use App\Services\IncomingMail\Contracts\MailParserInterface;

/**
 * Resolves a parser class by looking up the sender email in
 * config('incoming_mail.parsers'). Handles "Name <email>" format.
 */
class ParserResolver
{
    public function resolve(string $from): ?MailParserInterface
    {
        $email = $this->extractEmail($from);
        $parsers = config('incoming_mail.parsers', []);

        $parserClass = $parsers[$email] ?? null;

        if ($parserClass === null || ! class_exists($parserClass)) {
            return null;
        }

        return app($parserClass);
    }

    private function extractEmail(string $from): string
    {
        // Handle "Display Name <email@example.com>" format
        if (preg_match('/<([^>]+)>/', $from, $matches)) {
            return strtolower(trim($matches[1]));
        }

        return strtolower(trim($from));
    }
}
