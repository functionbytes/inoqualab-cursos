<?php

namespace App\Exports\Enterprises;

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

    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    public function query()
    {
        return $this->exam?->answers();
    }

    public function map($row): array
    {

        return [
            ExamQuestion::id($row->question_id)->question,
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
