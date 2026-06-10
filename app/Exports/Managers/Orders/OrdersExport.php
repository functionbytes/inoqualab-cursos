<?php

namespace App\Exports\Managers\Orders;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class OrdersExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $enterprise;

    private $distributor;

    private $type;

    private $method;

    private $condition;

    private $start;

    private $end;

    public function __construct($distributor, $enterprise, $type, $method, $condition, $start, $end)
    {

        $this->distributor = $distributor;
        $this->enterprise = $enterprise;
        $this->type = $type;
        $this->method = $method;
        $this->condition = $condition;
        $this->start = $start;
        $this->end = $end;

    }

    public function query()
    {

        $query = DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->join('inscriptions', function ($join) {
                $join->on('users.id', '=', 'inscriptions.user_id');
            })->join('orders', function ($join) {
                $join->on('inscriptions.order_id', '=', 'orders.id');
            })->join('orders_activity', function ($join) {
                $join->on('orders.id', '=', 'orders_activity.order_id');
            })->join('enterprises', function ($join) {
                $join->on('enterprises.id', '=', 'orders_activity.enterprise_id');
            })->join('distributors', function ($join) {
                $join->on('distributors.id', '=', 'orders_activity.distributor_id');
            });

        if ($this->enterprise != 0) {
            $query->where('enterprise_user.enterprise_id', '=', $this->enterprise);
        }

        if ($this->distributor != 0) {
            $query->where('orders_activity.distributor_id', '=', $this->distributor);
        }

        if ($this->condition != 0) {
            $query->where('orders.condition_id', '=', $this->condition);
        }

        if ($this->method != 0) {
            $query->where('orders.method_id', '=', $this->method);
        }

        if ($this->type != 0) {
            $query->where('orders.type_id', '=', $this->type);
        }

        return $query->whereBetween('orders.created_at', [$this->start, $this->end])
            ->select(
                'users.slack',
                'users.firstname',
                'users.lastname',
                'users.available',
                'users.cellphone',
                'users.address',
                'users.identification',
                'users.email',
                'inscriptions.course_id AS inscription_course',
                'inscriptions.enroll_start AS inscription_enroll_start',
                'inscriptions.enroll_expire AS inscription_enroll_expire',
                'inscriptions.enroll_culminated AS inscription_enroll_culminated',
                'orders_activity.enterprise_id AS orders_activity_enterprise',
                'orders_activity.distributor_id AS orders_activity_distributor',
                'distributors.title as distributor',
                'enterprises.title as enterprise',
                'orders.id AS order_id',
                'orders.slack AS order_slack',
                'orders.reference AS orders_reference',
                'orders.number AS order_number',
                'orders.method_id AS order_method',
                'orders.condition_id AS order_condition',
                'orders.type_id AS order_type',
                'orders.payment_at AS order_payment_at',
                'orders.created_at AS order_created_at',
            )->orderBy('orders.created_at', 'desc');

    }

    public function map($row): array
    {

        return [
            $row->order_slack,
            $row->order_number,
            $row->orders_reference,
            date('Y-m-d', strtotime($row->inscription_enroll_start)),
            date('Y-m-d', strtotime($row->inscription_enroll_expire)),
            date('Y-m-d', strtotime($row->order_payment_at)),
            $row->order_method == null ? '' : strtoupper(OrderMethod::id($row->order_method)->title),
            $row->order_condition == null ? '' : strtoupper(OrderCondition::id($row->order_condition)->title),
            $row->order_type == null ? '' : strtoupper(OrderType::id($row->order_type)->title),
            $row->orders_activity_distributor == null ? '' : strtoupper(Distributor::id($row->orders_activity_distributor)->title),
            $row->orders_activity_enterprise == null ? '' : strtoupper(Enterprise::id($row->orders_activity_enterprise)->title),
            $row->inscription_course == null ? '' : strtoupper(Course::id($row->inscription_course)->title),
            strtoupper($row->firstname),
            strtoupper($row->lastname),
            $row->identification == null ? '' : $row->identification,
            $row->cellphone,
            strtoupper($row->email),
        ];

    }

    public function headings(): array
    {
        return [
            'SLACK',
            'NUMERO',
            'REFERENCIA',
            'FECHA DE INICIO',
            'FECHA DE FINAL',
            'FECHA DE PAGO',
            'METODO DE PAGO',
            'ESTADO ORDEN',
            'TIPO ORDEN',
            'DISTRIBUIDOR',
            'EMPRESA',
            'COURSE',
            'NOMBRES',
            'APELLIDOS',
            'IDENTIFICACIÓN',
            'CELULAR',
            'EMAIL',
        ];
    }
}
