<?php

namespace App\Exports\Distributors;

use App\Models\Exam\ExamAnswer;
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
        // Certificados sin examen asociado (exam_id null, 4 casos reales):
        // $this->exam?->answers() da null y Maatwebsite\Excel revienta con
        // "__clone method called on non-object". Query que nunca matchea en
        // vez de null, para que el archivo se descargue vacío.
        return $this->exam?->answers() ?? ExamAnswer::whereRaw('1 = 0');
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
