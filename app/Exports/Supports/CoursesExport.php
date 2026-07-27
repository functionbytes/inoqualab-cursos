<?php

namespace App\Exports\Supports;

use App\Models\Enterprise\EnterpriseCourse;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class CoursesExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $course;

    private $enterprise;

    private $modalitie;

    public function __construct($course, $enterprise, $modalitie)
    {
        $this->modalitie = $modalitie;
        $this->course = $course;
        $this->enterprise = $enterprise;
    }

    public function query()
    {
        // Sin `default` el método devolvía null cuando la modalidad no era
        // 0/1/2 (p. ej. al entrar a la URL de generación sin parámetros), y
        // FromQuery reventaba con "__clone method called on non-object".
        // '0' (todos los usuarios) es el listado más amplio y sirve de base.
        return match ((string) $this->modalitie) {
            '1' => EnterpriseCourse::exportTerminated($this->enterprise, $this->course)->take(9999999),
            '2' => EnterpriseCourse::exportEarring($this->enterprise, $this->course)->take(9999999),
            default => EnterpriseCourse::exportUsers($this->enterprise, $this->course)->take(9999999),
        };
    }

    public function map($row): array
    {

        return [
            $row->firstname,
            $row->lastname,
            $row->identification != null ? $row->identification : '',
            $row->percent,
            $row->culminated_at != null ? date('Y-m-d', strtotime($row->created_at)) : 'PENDIENTE',
            $row->culminated_at != null ? date('Y-m-d', strtotime($row->culminated_at)) : 'PENDIENTE',
        ];

    }

    public function headings(): array
    {
        return [
            'NOMBRES',
            'APELLIDOS',
            'IDENTIFICACIÓN',
            'PORCENTAJE',
            'FECHA INICIO',
            'FECHA CULMINACIÓN',
        ];
    }
}
