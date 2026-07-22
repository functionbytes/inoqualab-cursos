<?php

namespace App\Exports\Distributors;

use App\Models\Distributor\Distributor;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class StaffExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    public function __construct(
        private readonly Distributor $distributor,
        private readonly ?string $available,
        private readonly Carbon $start,
        private readonly Carbon $end,
    ) {}

    public function query()
    {
        $staff = $this->distributor->staffs()
            ->whereBetween('users.created_at', [$this->start, $this->end]);

        if ($this->available !== null && $this->available !== '') {
            $staff->where('users.available', $this->available);
        }

        return $staff->orderBy('users.created_at', 'desc');
    }

    public function map($row): array
    {
        return [
            $row->firstname,
            $row->lastname,
            $row->identification ?? '',
            $row->cellphone,
            $row->email,
            $row->available ? 'Activo' : 'Inactivo',
            date('Y-m-d', strtotime($row->created_at)),
        ];
    }

    public function headings(): array
    {
        return [
            'NOMBRES',
            'APELLIDOS',
            'IDENTIFICACIÓN',
            'CELULAR',
            'EMAIL',
            'ESTADO',
            'FECHA REGISTRO',
        ];
    }
}
