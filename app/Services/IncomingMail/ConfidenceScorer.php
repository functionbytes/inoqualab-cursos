<?php

namespace App\Services\IncomingMail;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;

/**
 * Scores a parsed payload (0–100) to decide if it can be auto-processed.
 *
 * Scoring:
 *   +20  document present and numeric
 *   +10  name present
 *   +30  enterprise matched (not null)
 *   +40  ALL courses matched AND at least 1 course
 *
 * A fully-matched payload from the RedNacional sample gives 100.
 * The config threshold default is 90.
 */
class ConfidenceScorer
{
    /**
     * @param  array  $payload  Normalized payload from a parser.
     * @param  Enterprise|null  $enterprise  Matched enterprise (or null).
     * @param  (Course|null)[]  $courseMatches  Parallel array to payload['courses'].
     */
    public function score(array $payload, ?Enterprise $enterprise, array $courseMatches): int
    {
        $score = 0;

        if ($this->hasNumericDocument($payload)) {
            $score += 20;
        }

        if (! empty($payload['name'])) {
            $score += 10;
        }

        if ($enterprise !== null) {
            $score += 30;
        }

        if ($this->allCoursesMatched($payload, $courseMatches)) {
            $score += 40;
        }

        return $score;
    }

    private function hasNumericDocument(array $payload): bool
    {
        $doc = $payload['document'] ?? null;

        return $doc !== null && $doc !== '' && ctype_digit((string) $doc);
    }

    private function allCoursesMatched(array $payload, array $courseMatches): bool
    {
        $courses = $payload['courses'] ?? [];

        if (empty($courses)) {
            return false;
        }

        foreach ($courseMatches as $match) {
            if (! ($match instanceof Course)) {
                return false;
            }
        }

        return true;
    }
}
