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

        $users = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $this->enterprise)
            ->join('course_user', function ($join) {
                $join->on('users.id', '=', 'course_user.user_id');
            })->join('orders', function ($join) {
                $join->on('orders.id', '=', 'course_user.order_id');
            })->where('course_user.course_id', '=', $this->course);

        if ($this->modalitie == '1') {
            $users->where('course_user.culminated', '=', 1);
        } elseif ($this->modalitie == '2') {
            $users->where('course_user.culminated', '=', 0);
        }

        return $users->select(
            'users.slack',
            'users.firstname',
            'users.lastname',
            'users.available',
            'users.identification',
            'course_user.id',
            'orders.enroll_start',
            'orders.enroll_expire',
            'course_user.percent',
            'course_user.order_id',
            'course_user.updated_at',
            'course_user.culminated_at',
            'course_user.culminated',
            'course_user.created_at'
        )->orderBy('culminated_at', 'desc');

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
