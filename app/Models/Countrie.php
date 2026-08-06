<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Countrie extends Model
{
    use HasFactory;

    protected $table = 'countries';

    // La tabla no tiene created_at/updated_at (dato geográfico de referencia,
    // sembrado por import, nunca por la app): sin esto, cualquier create()
    // futuro tira QueryException por columna inexistente.
    public $timestamps = false;

    protected $fillable = [
        'title',
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
}
