<?php

namespace App\Exports\Accountings\Invoices;

use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class InvoicesExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $condition;

    private $distributor;

    private $method;

    private $start;

    private $end;

    /** Catálogos precargados (id => title) para no consultar por cada fila del export. */
    private array $methods;

    private array $conditions;

    public function __construct($distributor, $method, $condition, $start, $end)
    {
        $this->distributor = $distributor;
        $this->method = $method;
        $this->condition = $condition;
        $this->start = $start;
        $this->end = $end;

        $this->methods = InvoiceMethod::pluck('title', 'id')->all();
        $this->conditions = InvoiceCondition::pluck('title', 'id')->all();
    }

    public function query()
    {
        $invoices = DB::table('invoices')
            ->whereBetween('invoices.created_at', [$this->start, $this->end]);

        if ($this->distributor != 0) {
            $invoices = $invoices->join('distributors', 'distributors.id', '=', 'invoices.distributor_id')
                ->where('distributors.id', '=', $this->distributor);
        } else {
            $invoices = $invoices->join('distributors', 'distributors.id', '=', 'invoices.distributor_id');
        }

        if ($this->method != 0) {
            $invoices = $invoices->where('invoices.method_id', '=', $this->method);
        }

        if ($this->condition != 0) {
            $invoices = $invoices->where('invoices.condition_id', '=', $this->condition);
        }

        return $invoices->select(
            'distributors.slack',
            'distributors.title',
            'distributors.nit',
            'distributors.email',
            'invoices.method_id',
            'invoices.condition_id',
            'invoices.slack',
            'invoices.number',
            'invoices.reference',
            'invoices.from_at',
            'invoices.to_at',
            'invoices.payment_at',
            'invoices.created_at'
        )->orderBy('invoices.created_at', 'asc');
    }

    public function map($row): array
    {
        return [
            $row->slack,
            $row->title,
            $row->number,
            $row->reference,
            $row->nit,
            strtoupper($this->methods[$row->method_id] ?? ''),
            strtoupper($this->conditions[$row->condition_id] ?? ''),
            // Null-safe: una fecha nula (p.ej. factura sin pagar) daba "1969-12-31"
            // por strtotime(null), además del deprecation warning en PHP 8.1+.
            $row->from_at ? date('Y-m-d', strtotime($row->from_at)) : '',
            $row->to_at ? date('Y-m-d', strtotime($row->to_at)) : '',
            $row->payment_at ? date('Y-m-d', strtotime($row->payment_at)) : '',
            $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : '',
        ];
    }

    public function headings(): array
    {
        return [
            'SLACK',
            'DISTRIBUIDOR',
            'NUMERO',
            'REFERENCIA',
            'NIT',
            'METODO',
            'ESTADO',
            'FECHA DESDE',
            'FECHA HASTA',
            'FECHA PAGO',
            'FECHA CREACIÓN',
        ];
    }
}
