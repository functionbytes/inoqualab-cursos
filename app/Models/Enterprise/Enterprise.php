<?php

namespace App\Models\Enterprise;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enterprise extends Model
{
    use HasFactory,
        HasFinders, LogsActivity, SoftDeletes;

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $table = 'enterprises';

    protected $fillable = [
        'slack',
        'code',
        'title',
        'address',
        'cellphone',
        'nit',
        'leading',
        'supporting',
        'available',
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

    }

    public function scopeDescending($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('updated_at', 'asc');
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    /**
     * Método estático (no scope local) para no caer en el antipatrón de
     * Eloquent donde un `null` devuelto por un scope es reemplazado por el
     * propio Builder (`$result ?? $this` en Builder::callScope()).
     */
    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public function distributor(): HasOneThrough
    {
        return $this->hasOneThrough(
            'App\Models\Distributor\Distributor',  // Modelo de destino (Distributor)
            'App\Models\Distributor\DistributorEnterprise',  // Modelo intermedio (DistributorEnterprise)
            'enterprise_id',  // Clave foránea en la tabla intermedia (DistributorEnterprise)
            'id',  // Clave primaria del modelo de destino (Distributor)
            'id',  // Clave primaria en el modelo actual (Enterprise)
            'distributor_id'  // Clave foránea en la tabla intermedia que apunta al distribuidor
        );
    }

    public function staffs(): BelongsToMany
    {
        // orderBy calificado con la tabla: users y enterprise_staff tienen
        // ambas 'updated_at' -- sin calificar, un where()->exists() sobre esta
        // relación revienta con "Column 'updated_at' in ORDER BY is ambiguous"
        // (MySQL, no SQLite) en vez de devolver el resultado.
        return $this->belongsToMany('App\Models\User', 'enterprise_staff')->withPivot('enterprise_id')->orderBy('users.updated_at', 'desc');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'enterprise_user')->withPivot('enterprise_id')->orderBy('users.updated_at', 'desc');
    }

    public function courses(): BelongsToMany
    {
        // orderBy calificado con la tabla: courses y enterprise_course tienen
        // ambas 'updated_at' -- sin calificar, un where()->exists() sobre esta
        // relación revienta con "Column 'updated_at' in ORDER BY is ambiguous"
        // (MySQL, no SQLite) en vez de devolver el resultado. Bug real: rompía
        // includes() (matrícula masiva de Distributors/Supports\Enterprises\CourseController)
        // para cualquier empresa con al menos un curso asignado.
        return $this->belongsToMany('App\Models\Course\Course', 'enterprise_course')->withPivot('enterprise_id')->orderBy('courses.updated_at', 'desc');
    }

    public function rates(): HasMany
    {
        return $this->hasMany('App\Models\Enterprise\EnterpriseCourse', 'enterprise_id')->orderBy('updated_at', 'desc');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(EnterpriseAlias::class, 'enterprise_id');
    }
}
