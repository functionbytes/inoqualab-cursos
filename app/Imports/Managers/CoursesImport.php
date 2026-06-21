<?php

namespace App\Imports\Managers;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Inscription;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Method;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoursesImport implements ToCollection, WithHeadingRow
{
    use Importable;

    private readonly Enterprise $enterprise;

    private readonly Course $course;

    private readonly InvoiceCondition $condition;

    private readonly Method $method;

    public function __construct(string $enterpriseSlack, string $courseSlack)
    {
        $this->enterprise = Enterprise::slack($enterpriseSlack);
        $this->course = Course::slack($courseSlack);
        $this->condition = InvoiceCondition::slug('pagada');
        $this->method = Method::slug('acuerdo');
    }

    public function collection(Collection $rows): void
    {
        if ($this->enterprise->users()->doesntExist()) {
            return;
        }

        $now = Carbon::now()->setTimezone('America/Bogota');

        foreach ($rows as $row) {
            $user = User::query()
                ->where('identification', $row['identification'])
                ->first();

            if (! $user || $user->relations === null) {
                continue;
            }

            DB::transaction(function () use ($user, $now): void {
                $order = Order::create([
                    'slack' => Controller::generate_slack('users'),
                    'subtotal' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'transaction' => null,
                    'condition_id' => $this->condition->id,
                    'method_id' => $this->method->id,
                    'course_id' => $this->course->id,
                    'user_id' => $user->id,
                    'enroll_start' => $now,
                    'enroll_expire' => $now->copy()->addMonths(3),
                ]);

                Inscription::create([
                    'user_id' => $user->id,
                    'course_id' => $this->course->id,
                    'order_id' => $order->id,
                    'culminated' => 0,
                    'culminated_at' => null,
                ]);
            });
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
