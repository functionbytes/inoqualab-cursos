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

    protected $fillable = [
        'title',
        'country_id',
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
