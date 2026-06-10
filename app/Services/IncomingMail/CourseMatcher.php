<?php

namespace App\Services\IncomingMail;

use App\Models\Course\Course;
use App\Models\Course\CourseAlias;
use App\Models\Enterprise\Enterprise;
use Illuminate\Support\Str;

class CourseMatcher
{
    /**
     * Attempt to match a Course from a raw course name string.
     *
     * Strategy (first match wins):
     * 1. CourseAlias by normalized_alias
     * 2. If enterprise given, compare normalized title against enterprise courses
     * 3. Global Course title LIKE fallback
     */
    public function match(string $courseText, ?Enterprise $enterprise = null): ?Course
    {
        $normalized = $this->normalize($courseText);

        $alias = CourseAlias::query()
            ->where('normalized_alias', $normalized)
            ->with('course')
            ->first();

        if ($alias?->course instanceof Course) {
            return $alias->course;
        }

        if ($enterprise !== null) {
            $course = $enterprise->courses()
                ->get()
                ->first(fn ($c) => $this->normalize($c->title) === $normalized);

            if ($course instanceof Course) {
                return $course;
            }
        }

        return $this->findByTitle($normalized);
    }

    public function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strtolower(Str::ascii($value))));
    }

    private function findByTitle(string $normalized): ?Course
    {
        return Course::query()
            ->whereRaw('LOWER(title) LIKE ?', ['%'.str_replace(['%', '_'], ['\%', '\_'], $normalized).'%'])
            ->first();
    }
}
