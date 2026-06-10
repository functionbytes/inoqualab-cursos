<?php

namespace App\Services\IncomingMail\Contracts;

interface MailParserInterface
{
    /**
     * Parse a raw email into a normalized payload array.
     *
     * Returned keys:
     *   - document (string|null)         : numeric identification
     *   - name (string|null)             : full name as received
     *   - firstname (string|null)        : best-effort first name(s)
     *   - lastname (string|null)         : best-effort last name(s)
     *   - enterprise_raw (string|null)   : raw enterprise string from email
     *   - enterprise_name (string|null)  : extracted legal name
     *   - enterprise_code (string|null)  : grouper code (e.g. "C00")
     *   - courses_raw (string|null)      : raw courses string from email
     *   - courses (string[])             : clean course name array
     *   - ips (string|null)              : referring IPS
     *   - meta (array)                   : any extra fields not mapped above
     */
    public function parse(string $subject, string $body): array;
}
