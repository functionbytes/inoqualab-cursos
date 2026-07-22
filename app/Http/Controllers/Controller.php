<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Reinscripción idempotente para los flujos administrativos: si el usuario ya
     * tiene una inscripción para el curso, la REUTILIZA (extiende su vigencia y
     * renueva el certificado un año más, igual que CheckoutController::enrollCourse);
     * si no, crea una nueva. Devuelve [Inscription $inscription, bool $isNew].
     * Debe llamarse DENTRO de la transacción que crea la orden (ya con $orderId).
     */
    protected function renewOrCreateInscription(int $userId, int $courseId, int $orderId, Carbon $now): array
    {
        $inscription = Inscription::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($inscription) {
            $inscription->enroll_start = $now;
            $inscription->enroll_expire = $now->copy()->addMonths(3);
            $inscription->expire = 0;
            $inscription->order_id = $orderId;
            $inscription->updated_at = $now;
            $inscription->save();

            $certificate = Certificate::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->latest('id')
                ->first();

            if ($certificate) {
                // Si el certificado sigue vigente, se extiende desde su vencimiento;
                // si ya venció, desde hoy. Siempre +1 año.
                $current = $certificate->end_at ? Carbon::parse($certificate->end_at) : $now;
                $base = $current->isFuture() ? $current : $now;
                $certificate->start_at = $now;
                $certificate->end_at = $base->copy()->addYear();
                $certificate->save();
            }

            return [$inscription, false];
        }

        $inscription = new Inscription;
        $inscription->slack = $this->generate_slack('inscriptions');
        $inscription->order_id = $orderId;
        $inscription->user_id = $userId;
        $inscription->course_id = $courseId;
        $inscription->percent = 0;
        $inscription->enroll_start = $now;
        $inscription->enroll_expire = $now->copy()->addMonths(3);
        $inscription->enroll_culminated = null;
        $inscription->culminated = 0;
        $inscription->created_at = $now;
        $inscription->updated_at = $now;
        $inscription->save();

        return [$inscription, true];
    }

    protected function availableOptions(bool $withBlank = false): Collection
    {
        $options = collect(['1' => 'Publico', '0' => 'Oculto']);

        if ($withBlank) {
            $options->prepend('', '');
        }

        return $options;
    }

    public function generate_slack($table)
    {
        do {
            $slack = Str::random(6);
            $exist = DB::table($table)->where('slack', $slack)->exists();
        } while ($exist);

        return $slack;
    }

    public function generate_number($table)
    {
        $lastId = DB::table($table)->max('id');

        return $lastId ? $lastId + 1 : 1;

    }
}
