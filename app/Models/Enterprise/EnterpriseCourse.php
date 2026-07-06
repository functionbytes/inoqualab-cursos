<?php

namespace App\Models\Enterprise;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseCourse extends Model
{
    use HasFactory;

    protected $table = 'enterprise_course';

    protected $fillable = [
        'course_id',
        'enterprise_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Método estático (no scope local) para no caer en el antipatrón de
     * Eloquent donde un `null` devuelto por un scope es reemplazado por el
     * propio Builder (`$result ?? $this` en Builder::callScope()).
     */
    public static function validate($enterprise, $course): ?self
    {
        return static::where('course_id', $course)->where('enterprise_id', $enterprise)->first();
    }

    /** Estudiantes de una empresa inscritos en un curso, con su progreso. */
    private static function studentsQuery($enterpriseId, $courseId): Builder
    {
        return User::query()
            ->join('enterprise_user', fn ($j) => $j->on('users.id', '=', 'enterprise_user.user_id'))
            ->where('enterprise_user.enterprise_id', $enterpriseId)
            ->join('inscriptions', fn ($j) => $j->on('users.id', '=', 'inscriptions.user_id'))
            ->where('inscriptions.course_id', $courseId)
            ->select(
                'users.firstname',
                'users.lastname',
                'users.identification',
                'inscriptions.percent',
                'inscriptions.created_at',
                'inscriptions.enroll_culminated as culminated_at',
            );
    }

    /** Todos los estudiantes de la empresa inscritos en el curso. */
    public static function exportUsers($enterpriseId, $courseId): Builder
    {
        return static::studentsQuery($enterpriseId, $courseId);
    }

    /** Estudiantes que ya culminaron el curso. */
    public static function exportTerminated($enterpriseId, $courseId): Builder
    {
        return static::studentsQuery($enterpriseId, $courseId)->where('inscriptions.culminated', 1);
    }

    /** Estudiantes pendientes de culminar el curso. */
    public static function exportEarring($enterpriseId, $courseId): Builder
    {
        return static::studentsQuery($enterpriseId, $courseId)->where('inscriptions.culminated', 0);
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }
}
