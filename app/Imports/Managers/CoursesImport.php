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
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoursesImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public $enterprise;

    public $course;

    public function __construct($enterprise, $course)
    {
        $this->enterprise = $enterprise;
        $this->course = $course;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $enterprise = Enterprise::slack($this->enterprise);
            $course = Course::slack($this->course);

            $users = $enterprise->users;

            if (count($users) > 0) {

                $identification = $row['identification'];
                $user = User::where('identification', $identification)->firstOrFail();

                $validate = $user->relations;

                if ($validate != null) {

                    $condition = InvoiceCondition::slug('pagada');
                    $method = Method::slug('acuerdo');
                    $date = new Carbon;

                    $order = new Order;
                    $order->slack = Controller::generate_slack('users');
                    $order->subtotal = 0;
                    $order->discount = 0;
                    $order->total = 0;
                    $order->transaction = null;
                    $order->condition_id = $condition->id;
                    $order->method_id = $method->id;
                    $order->course_id = $course->id;
                    $order->user_id = $user->id;
                    $order->enroll_start = Carbon::now()->setTimezone('America/Bogota');
                    $order->enroll_expire = Carbon::now()->setTimezone('America/Bogota')->addMonths(3);
                    $order->created_at = Carbon::now()->setTimezone('America/Bogota');
                    $order->updated_at = Carbon::now()->setTimezone('America/Bogota');
                    $order->save();

                    $include = new Inscription;
                    $include->user_id = $user->id;
                    $include->course_id = $course->id;
                    $include->order_id = $order->id;
                    $include->culminated = 0;
                    $include->culminated_at = null;
                    $include->created_at = Carbon::now()->setTimezone('America/Bogota');
                    $include->updated_at = Carbon::now()->setTimezone('America/Bogota');
                    $include->save();

                }

            }

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
