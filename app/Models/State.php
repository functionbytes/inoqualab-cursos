<?php

namespace App\Models;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'states';

    // La tabla no tiene created_at/updated_at (dato geográfico de referencia,
    // sembrado por import, nunca por la app): sin esto, cualquier create()
    // futuro tira QueryException por columna inexistente.
    public $timestamps = false;

    // La columna real es `countrie_id` (coincide con la FK de countrie()) --
    // 'country_id' no existe en la tabla; nadie crea States vía mass
    // assignment hoy, pero el primer create(['country_id' => ...]) hubiera
    // tirado un QueryException por columna inexistente.
    protected $fillable = [
        'title',
        'countrie_id',
        'created_at',
        'updated_at',
    ];

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeId($query, $countrie)
    {
        $model = $query->where('id', $countrie)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function countrie(): BelongsTo
    {
        return $this->belongsTo('App\Models\Countrie', 'countrie_id', 'id');
    }
}
