<?php

namespace App\Imports\Managers;

use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public $token;

    public function __construct($enterprise)
    {
        $this->token = $enterprise;
    }

    public function rules(): array
    {
        return [
            '*.email' => ['email', 'unique:users,email'],
            '*.identification' => ['unique:users,identification'],
        ];
    }

    public function customValidationMessages()
    {
        return [

            '*.email.required' => 'Email requerido!',
            '*.email.email' => 'Email incorrecto!',
            '*.email.unique' => 'Email repetido',

            '*.identification.required' => 'Identificacion requerido!',
            '*.identification.unique' => 'Identificacion repetida',

        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $enterprise = Enterprise::slack($this->token);

            $user = new User;
            $user->slack = Str::uuid()->toString();
            $user->firstname = $row['firstname'];
            $user->lastname = $row['lastname'];
            $user->cellphone = $row['cellphone'];
            $user->identification = $row['identification'];
            $user->address = $row['address'];
            $user->email = $row['email'];
            $user->password = $row['password'];
            $user->terms = 1;
            $user->available = 1;
            $user->validation = 0;
            $user->setting = 0;
            $user->role = 'customer';
            $user->remember_token = Str::random(60);
            $user->email_verified_at = Carbon::now()->setTimezone('America/Bogota');
            $user->created_at = Carbon::now()->setTimezone('America/Bogota');
            $user->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $user->save();

            $inscription = new EnterpriseUser;
            $inscription->user_id = $user->id;
            $inscription->enterprise_id = $enterprise->id;
            $inscription->available = 1;
            $inscription->created_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->updated_at = Carbon::now()->setTimezone('America/Bogota');
            $inscription->save();
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
