<?php

namespace App\Models\Order;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMethod extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'order_method';

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

    public function orders()
    {
        return $this->hasMany('App\Models\Order\Order');
    }
}
