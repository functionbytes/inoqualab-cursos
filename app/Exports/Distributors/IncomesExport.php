<?php

namespace App\Exports\Distributors;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\OrderMethod;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class IncomesExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $enterprise;

    private $course;

    private $start;

    private $end;

    /** Catálogos precargados (id => title) para no consultar por cada fila del export. */
    private array $methods;

    private array $distributors;

    private array $enterprises;

    private array $courses;

    public function __construct($enterprise, $course, $start, $end)
    {
        $this->enterprise = $enterprise;
        $this->course = $course;
        $this->start = $start;
        $this->end = $end;

        $this->methods = OrderMethod::pluck('title', 'id')->all();
        $this->distributors = Distributor::pluck('title', 'id')->all();
        $this->enterprises = Enterprise::pluck('title', 'id')->all();
        $this->courses = Course::pluck('title', 'id')->all();
    }

    public function query()
    {

        $query = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })
            ->where('enterprise_user.enterprise_id', '=', $this->enterprise)
            ->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })
            ->join('orders', function ($join) {
                $join->on('inscriptions.order_id', '=', 'orders.id');
            })->join('orders_activity', function ($join) {
                $join->on('orders.id', '=', 'orders_activity.order_id');
            });

        // Aplica el filtro de course_id solo si no es 0
        if ($this->course != 0) {
            $query->where('inscriptions.course_id', '=', $this->course);
        }

        return $query->whereBetween('inscriptions.created_at', [$this->start, $this->end])
            ->select(
                'users.slack',
                'users.firstname',
                'users.lastname',
                'users.identification',
                'inscriptions.course_id AS inscription_course',
                'inscriptions.enroll_expire AS inscription_enroll_expire',
                'inscriptions.enroll_culminated AS inscription_enroll_culminated',
                'orders_activity.enterprise_id AS orders_activity_enterprise',
                'orders_activity.distributor_id AS orders_activity_distributor',
                'orders.id AS order_id',
                'orders.slack AS order_slack',
                'orders.number AS order_number',
                'orders.method_id AS order_method',
                'orders.payment_at AS order_payment_at',
            )->orderBy('inscriptions.enroll_culminated', 'desc');
    }

    public function map($row): array
    {

        return [
            $row->order_slack,
            $row->order_number,
            $row->inscription_enroll_expire,
            $row->inscription_enroll_culminated != null ? $row->inscription_enroll_culminated : 'NO FINALIZADO',
            $row->order_payment_at,
            strtoupper($this->methods[$row->order_method] ?? ''),
            strtoupper($this->courses[$row->inscription_course] ?? ''),
            $row->orders_activity_distributor != null ? strtoupper($this->distributors[$row->orders_activity_distributor] ?? '') : '',
            $row->orders_activity_enterprise != null ? strtoupper($this->enterprises[$row->orders_activity_enterprise] ?? '') : '',
            strtoupper($row->firstname),
            strtoupper($row->lastname),
            $row->identification == null ? '' : $row->identification,
        ];

    }

    public function headings(): array
    {
        return [
            'ORDEN',
            'ORDEN NUMERO',
            'FECHA INICIO',
            'FECHA FINAL',
            'FECHA PAGO',
            'METODO DE PAGO',
            'CURSO',
            'DISTRIBUIDOR',
            'EMPRESA',
            'NOMBRES',
            'APELLIDOS',
            'IDENTIFICACIÓN',
        ];
    }
}
