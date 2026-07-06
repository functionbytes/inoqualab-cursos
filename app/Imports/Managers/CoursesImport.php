<?php

namespace App\Imports\Managers;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use App\Services\InscriptionService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CoursesImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    private readonly Enterprise $enterprise;

    private readonly Course $course;

    public function __construct(string $enterpriseSlack, string $courseSlack)
    {
        $this->enterprise = Enterprise::slack($enterpriseSlack);
        $this->course = Course::slack($courseSlack);
    }

    public function rules(): array
    {
        return [
            '*.identification' => ['required'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '*.identification.required' => 'Identificacion requerida!',
        ];
    }

    public function collection(Collection $rows): void
    {
        if ($this->enterprise->users()->doesntExist()) {
            return;
        }

        // Solo se matriculan identificaciones que pertenecen a $this->enterprise:
        // evita matricular usuarios de otra empresa o sin relacion (antes se
        // aceptaba cualquier identificacion con ALGUNA relacion de empresa).
        $memberIdentifications = $this->enterprise->users()->pluck('users.identification');

        $inscriptionService = app(InscriptionService::class);

        foreach ($rows as $row) {
            if (! $memberIdentifications->contains($row['identification'])) {
                continue;
            }

            $user = User::query()
                ->where('identification', $row['identification'])
                ->first();

            if (! $user) {
                continue;
            }

            $inscriptionService->enrollSimple($user, $this->course);
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
