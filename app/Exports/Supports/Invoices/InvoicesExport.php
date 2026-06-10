<?php

namespace App\Exports\Supports\Invoices;

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

    public function __construct($distributor, $method, $condition, $start, $end)
    {
        $this->distributor = $distributor;
        $this->method = $method;
        $this->condition = $condition;
        $this->start = $start;
        $this->end = $end;
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
        )->orderBy('invoices.created_at', 'desc');
    }

    public function map($row): array
    {
        return [
            $row->slack,
            $row->title,
            $row->number,
            $row->reference,
            $row->nit,
            strtoupper(InvoiceMethod::id($row->method_id)->label),
            strtoupper(InvoiceCondition::id($row->condition_id)->label),
            date('Y-m-d', strtotime($row->from_at)),
            date('Y-m-d', strtotime($row->to_at)),
            date('Y-m-d', strtotime($row->payment_at)),
            date('Y-m-d', strtotime($row->created_at)),
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
