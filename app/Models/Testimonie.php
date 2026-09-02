<?php

namespace App\Models;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonie extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'testimonies';

    protected $fillable = [
        'slack',
        'firstname',
        'lastname',
        'role',
        'icon',
        'rating',
        'benefit',
        'position',
        'counter_value',
        'counter_suffix',
        'description',
        'available',
        'created_at',
        'updated_at',
    ];

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Orden manual para el carrusel del home (columna `position`, no fecha de
     * creación): permite decidir qué testimonio aparece primero sin depender
     * de cuándo se creó el registro.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeAvailable($query)
    {
        // Sin ->get(): un scope debe devolver el Builder encadenable (p.ej.
        // Testimonie::available()->ordered()->limit(6)->get() en el home).
        return $query->where('available', 1);
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }
}
