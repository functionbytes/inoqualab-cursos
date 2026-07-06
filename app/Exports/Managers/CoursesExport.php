<?php

namespace App\Exports\Managers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
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

        // La matrícula real vive en inscriptions (no existe tabla course_user).
        $users = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $this->enterprise)
            ->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })->where('inscriptions.course_id', '=', $this->course);

        if ($this->modalitie == '1') {
            $users->where('inscriptions.culminated', '=', 1);
        } elseif ($this->modalitie == '2') {
            $users->where('inscriptions.culminated', '=', 0);
        }

        return $users->select(
            'users.slack',
            'users.firstname',
            'users.lastname',
            'users.available',
            'users.identification',
            'inscriptions.id',
            'inscriptions.enroll_start',
            'inscriptions.enroll_expire',
            'inscriptions.percent',
            'inscriptions.order_id',
            'inscriptions.updated_at',
            'inscriptions.enroll_culminated as culminated_at',
            'inscriptions.culminated',
            'inscriptions.created_at'
        )->orderBy('inscriptions.enroll_culminated', 'desc');

    }

    public function map($row): array
    {

        return [
            $row->lastname == null ? '' : $row->lastname,
            $row->firstname == null ? '' : $row->firstname,
            $row->identification == null ? '' : $row->identification,
            $row->percent >= 100 ? 100 : $row->percent,
            date('Y-m-d', strtotime($row->updated_at)),
            $row->culminated == 1 ? date('Y-m-d', strtotime($row->culminated_at)) : 'PENDIENTE',
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
