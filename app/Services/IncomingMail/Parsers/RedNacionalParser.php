<?php

namespace App\Services\IncomingMail\Parsers;

use App\Services\IncomingMail\Contracts\MailParserInterface;

/**
 * Parses the plain-text "label: value" format sent by San Diego / Red Nacional.
 *
 * Sample body:
 *   Fecha: 2026/06/09 - Hora: 08:35:55
 *   Documento: 1005038876
 *   Nombre: CALDERON ORTEGA SANDRA MILENA
 *   Empresa: DOMIORIENTE S.A.S./BPM (Agrupador: C00)
 *   Examenes a cancelar por la empresa: , BPM VIRTUAL (10 horas - 5 modulos)
 *   IPS que remite: San Diego (Bucaramanga)
 */
class RedNacionalParser implements MailParserInterface
{
    public function parse(string $subject, string $body): array
    {
        $body = $this->normalizeEncoding($body);
        $lines = explode("\n", $body);
        $fields = $this->extractFields($lines);

        $document = $this->extractDocument($fields);
        $name = $this->extractName($fields);
        [$firstname, $lastname] = $this->splitName($name);
        [$enterpriseName, $enterpriseCode] = $this->extractEnterprise($fields);
        [$coursesRaw, $courses] = $this->extractCourses($fields);
        $ips = $this->extractIps($fields);
        $meta = $this->buildMeta($fields);

        return [
            'document' => $document,
            'name' => $name,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'enterprise_raw' => $fields['empresa'] ?? null,
            'enterprise_name' => $enterpriseName,
            'enterprise_code' => $enterpriseCode,
            'courses_raw' => $coursesRaw,
            'courses' => $courses,
            'ips' => $ips,
            'meta' => $meta,
        ];
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function normalizeEncoding(string $text): string
    {
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        return $text;
    }

    /**
     * Walk each line and build a label => value map (lowercase labels, trimmed values).
     *
     * Lines like "Fecha: 2026/06/09 - Hora: 08:35:55" map to ['fecha' => '2026/06/09 - Hora: 08:35:55']
     * (everything after the first colon is kept as the value).
     */
    private function extractFields(array $lines): array
    {
        $fields = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $colonPos = strpos($line, ':');

            if ($colonPos === false) {
                continue;
            }

            $label = mb_strtolower(trim(substr($line, 0, $colonPos)));
            $value = trim(substr($line, $colonPos + 1));

            // Normalize common label variants
            $label = $this->normalizeLabel($label);
            $fields[$label] = $value;
        }

        return $fields;
    }

    private function normalizeLabel(string $label): string
    {
        $map = [
            'examenes a cancelar por la empresa' => 'examenes_empresa',
            'examenes a cancelar por el paciente' => 'examenes_paciente',
            'ips que remite' => 'ips',
            'nombre ips' => 'nombre_ips',
            'persona de contacto' => 'contacto',
        ];

        return $map[$label] ?? $label;
    }

    private function extractDocument(array $fields): ?string
    {
        $raw = $fields['documento'] ?? null;

        if ($raw === null) {
            return null;
        }

        // Keep digits only
        $digits = preg_replace('/\D/', '', $raw);

        return $digits !== '' ? $digits : null;
    }

    private function extractName(array $fields): ?string
    {
        $name = $fields['nombre'] ?? null;

        return $name !== '' ? $name : null;
    }

    /**
     * Colombian names commonly follow APELLIDO1 APELLIDO2 NOMBRE1 NOMBRE2.
     * Heuristic: first two tokens are last names, the rest are first names.
     * Works well for the common 4-word full-name format.
     */
    private function splitName(?string $name): array
    {
        if ($name === null || trim($name) === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) <= 1) {
            return [$parts[0] ?? null, null];
        }

        if (count($parts) === 2) {
            return [$parts[1], $parts[0]];
        }

        // 3+ parts: last 2 tokens = firstnames, first tokens = lastnames
        $lastnames = array_slice($parts, 0, 2);
        $firstnames = array_slice($parts, 2);

        return [implode(' ', $firstnames), implode(' ', $lastnames)];
    }

    /**
     * Parses "DOMIORIENTE S.A.S./BPM (Agrupador: C00)"
     * → ['DOMIORIENTE S.A.S.', 'C00']
     */
    private function extractEnterprise(array $fields): array
    {
        $raw = $fields['empresa'] ?? null;

        if ($raw === null || $raw === '') {
            return [null, null];
        }

        // Extract code from "(Agrupador: C00)"
        $code = null;
        if (preg_match('/\(Agrupador:\s*([^)]+)\)/i', $raw, $m)) {
            $code = trim($m[1]);
        }

        // Enterprise name is everything before the first "/"
        $slashPos = strpos($raw, '/');
        $name = $slashPos !== false ? trim(substr($raw, 0, $slashPos)) : trim($raw);

        return [$name ?: null, $code];
    }

    /**
     * Parses "Examenes a cancelar por la empresa: , BPM VIRTUAL (10 horas - 5 modulos)"
     * → raw string + ['BPM VIRTUAL']
     */
    private function extractCourses(array $fields): array
    {
        $raw = $fields['examenes_empresa'] ?? null;

        if ($raw === null || trim($raw) === '') {
            return [null, []];
        }

        $items = explode(',', $raw);
        $courses = [];

        foreach ($items as $item) {
            $item = trim($item);

            if ($item === '') {
                continue;
            }

            // Remove suffix like "(10 horas - 5 modulos)"
            $clean = preg_replace('/\s*\(.*?\)\s*$/', '', $item);
            $clean = trim($clean);

            if ($clean !== '') {
                $courses[] = $clean;
            }
        }

        return [$raw, $courses];
    }

    private function extractIps(array $fields): ?string
    {
        return $fields['ips'] ?? null;
    }

    /** Remaining fields not mapped to standard keys */
    private function buildMeta(array $fields): array
    {
        $standard = ['documento', 'nombre', 'empresa', 'examenes_empresa', 'examenes_paciente', 'ips', 'ocupacion'];

        return array_filter(
            $fields,
            fn ($key) => ! in_array($key, $standard, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
