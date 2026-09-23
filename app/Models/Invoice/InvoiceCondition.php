<?php

namespace App\Models\Invoice;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCondition extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'invoice_condition';

    /**
     * Clases Bootstrap del badge de estado, por slug de condicion — mismo
     * criterio que App\Models\Order\OrderCondition::badgeClass(), pero con
     * 'pagada' como slug real (invoice_condition no usa 'payment' como
     * order_condition). Sin este accessor, managers.views.invoices.invoices
     * renderizaba el badge sin clase bg-* ni text-*, con el texto invisible.
     */
    protected function badgeClass(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->slug) {
                'pagada' => 'bg-success-subtle text-success',
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

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice\Invoice');
    }
}
