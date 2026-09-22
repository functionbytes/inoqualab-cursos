<?php

namespace App\Models\Order;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCondition extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'order_condition';

    /**
     * Clases Bootstrap del badge de estado, por slug de condicion.
     * 'payment' es el slug real de "Pagada" (ver seeder/datos de order_condition).
     */
    protected function badgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'payment' => 'bg-success-subtle text-success',
                'pendiente' => 'bg-warning-subtle text-warning',
                'rechazada' => 'bg-danger-subtle text-danger',
                default => 'bg-secondary-subtle text-secondary',
            },
        );
    }

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];

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

    public function scopeSlug($query, $slug)
    {
        $model = $query->where('slug', $slug)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }
}
