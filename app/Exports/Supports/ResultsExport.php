<?php

namespace App\Exports\Supports;

use App\Models\Exam\ExamQuestion;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ResultsExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $exam;

    /** Preguntas precargadas (id => enunciado) para no consultar por cada fila del export. */
    private array $questions;

    public function __construct($exam)
    {
        $this->exam = $exam;

        $questionIds = $exam?->answers()->pluck('question_id') ?? collect();
        $this->questions = ExamQuestion::whereIn('id', $questionIds)->pluck('question', 'id')->all();
    }

    public function query()
    {
        return $this->exam?->answers();
    }

    public function map($row): array
    {

        return [
            $this->questions[$row->question_id] ?? '',
            $row->user_answer,
            $row->answer,
            $row->approved == 1 ? $result = 'Correcto' : $result = 'Incorrecto',
        ];

    }

    public function headings(): array
    {
        return [
            'PREGUNTA',
            'RESPUESTA USUARIO',
            'RESPUESTA',
            'ESTADO',
        ];
    }
}
