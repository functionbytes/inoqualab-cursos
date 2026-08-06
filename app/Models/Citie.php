<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Citie extends Model
{
    protected $table = 'cities';

    // La tabla no tiene created_at/updated_at (dato geográfico de referencia,
    // sembrado por import, nunca por la app): sin esto, cualquier create()
    // futuro tira QueryException por columna inexistente.
    public $timestamps = false;

    protected $fillable = [
        'title',
        'state_id',
    ];

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo('App\Models\State', 'state_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User');
    }
}
