<?php

namespace App\Exports\Distributors;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UsersExport implements FromQuery, Responsable, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    private $enterprise;

    private $modalitie;

    public function __construct($enterprise, $modalitie)
    {
        $this->enterprise = $enterprise;
        $this->modalitie = $modalitie;
    }

    public function query()
    {

        if ($this->modalitie == '1') {
            $users = $this->enterprise->users()->available();
        } elseif ($this->modalitie == '2') {
            // User no tiene scopeDisabled(); "Inactivos" es simplemente available = 0.
            $users = $this->enterprise->users()->where('users.available', 0);
        } else {
            $users = $this->enterprise->users();
        }

        return $users->orderBy('firstname', 'desc');
    }

    public function map($row): array
    {

        return [
            $row->firstname,
            $row->lastname,
            $row->identification != null ? $row->identification : '',
            $row->cellphone,
            $row->email,
            $row->address,
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
            'DIRECCIÓN',
            'FECHA REGISTRO',
        ];
    }
}
