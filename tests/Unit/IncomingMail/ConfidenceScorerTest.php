<?php

namespace Tests\Unit\IncomingMail;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Services\IncomingMail\ConfidenceScorer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfidenceScorerTest extends TestCase
{
    private ConfidenceScorer $scorer;

    /** Full valid payload matching the RedNacional sample */
    private array $fullPayload = [
        'document' => '1005038876',
        'name' => 'CALDERON ORTEGA SANDRA MILENA',
        'enterprise_name' => 'DOMIORIENTE S.A.S.',
        'enterprise_code' => 'C00',
        'courses' => ['BPM VIRTUAL'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->scorer = new ConfidenceScorer;
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private function makeEnterprise(): Enterprise
    {
        $e = new Enterprise;
        $e->title = 'DOMIORIENTE S.A.S.';

        return $e;
    }

    private function makeCourse(): Course
    {
        $c = new Course;
        $c->title = 'BPM VIRTUAL';

        return $c;
    }

    // ---------------------------------------------------------------------------
    // Happy path
    // ---------------------------------------------------------------------------

    public function test_todo_matcheado_da_score_alto(): void
    {
        // +20 document, +10 name, +30 enterprise, +40 courses = 100
        $score = $this->scorer->score(
            $this->fullPayload,
            $this->makeEnterprise(),
            [$this->makeCourse()]
        );

        $this->assertGreaterThanOrEqual(90, $score);
        $this->assertSame(100, $score);
    }

    // ---------------------------------------------------------------------------
    // Partial matches
    // ---------------------------------------------------------------------------

    public function test_curso_sin_match_baja_score(): void
    {
        // +20 document, +10 name, +30 enterprise, +0 courses (null match) = 60
        $score = $this->scorer->score(
            $this->fullPayload,
            $this->makeEnterprise(),
            [null]
        );

        $this->assertSame(60, $score);
        $this->assertLessThan(90, $score);
    }

    public function test_sin_empresa_baja_score(): void
    {
        // +20 document, +10 name, +0 enterprise, +40 courses = 70
        $score = $this->scorer->score(
            $this->fullPayload,
            null,
            [$this->makeCourse()]
        );

        $this->assertSame(70, $score);
        $this->assertLessThan(90, $score);
    }

    public function test_sin_documento_baja_score(): void
    {
        // +0 document, +10 name, +30 enterprise, +40 courses = 80
        $payload = array_merge($this->fullPayload, ['document' => null]);

        $score = $this->scorer->score(
            $payload,
            $this->makeEnterprise(),
            [$this->makeCourse()]
        );

        $this->assertSame(80, $score);
        $this->assertLessThan(90, $score);
    }

    // ---------------------------------------------------------------------------
    // Edge cases
    // ---------------------------------------------------------------------------

    public function test_score_cero_cuando_payload_vacio(): void
    {
        $score = $this->scorer->score([], null, []);

        $this->assertSame(0, $score);
    }

    public function test_documento_no_numerico_no_suma_puntos(): void
    {
        // document must be ctype_digit; 'ABC' is not numeric
        $payload = array_merge($this->fullPayload, ['document' => 'ABC']);

        $score = $this->scorer->score(
            $payload,
            $this->makeEnterprise(),
            [$this->makeCourse()]
        );

        // +0 document, +10 name, +30 enterprise, +40 courses = 80
        $this->assertSame(80, $score);
    }

    public function test_cursos_vacios_no_suman_los_40_puntos(): void
    {
        // payload['courses'] is empty → allCoursesMatched returns false
        $payload = array_merge($this->fullPayload, ['courses' => []]);

        $score = $this->scorer->score(
            $payload,
            $this->makeEnterprise(),
            []
        );

        // +20 document, +10 name, +30 enterprise, +0 courses = 60
        $this->assertSame(60, $score);
    }

    public function test_multiples_cursos_todos_matcheados_suma_40(): void
    {
        $payload = array_merge($this->fullPayload, ['courses' => ['CURSO A', 'CURSO B']]);

        $score = $this->scorer->score(
            $payload,
            $this->makeEnterprise(),
            [$this->makeCourse(), $this->makeCourse()]
        );

        $this->assertSame(100, $score);
    }

    public function test_un_curso_sin_match_entre_varios_impide_los_40_puntos(): void
    {
        $payload = array_merge($this->fullPayload, ['courses' => ['CURSO A', 'CURSO B']]);

        // Second course is null → allCoursesMatched = false
        $score = $this->scorer->score(
            $payload,
            $this->makeEnterprise(),
            [$this->makeCourse(), null]
        );

        // +20 document, +10 name, +30 enterprise, +0 courses = 60
        $this->assertSame(60, $score);
    }

    public function test_solo_nombre_sin_lo_demas(): void
    {
        // +0 document, +10 name, +0 enterprise, +0 courses = 10
        $score = $this->scorer->score(
            ['name' => 'SOLO NOMBRE'],
            null,
            []
        );

        $this->assertSame(10, $score);
    }

    #[DataProvider('documentProvider')]
    public function test_documento_numerico_vs_no_numerico(string $doc, bool $expectBonus): void
    {
        $payload = ['document' => $doc, 'name' => 'TEST', 'courses' => []];

        $score = $this->scorer->score($payload, null, []);

        // Base without doc = 10 (name only); with doc = 30
        if ($expectBonus) {
            $this->assertSame(30, $score);
        } else {
            $this->assertSame(10, $score);
        }
    }

    public static function documentProvider(): array
    {
        return [
            'digits only' => ['1005038876', true],
            'letters only' => ['ABCDEF', false],
            'mixed alpha-num' => ['123ABC', false],
            'empty string' => ['', false],
        ];
    }
}
