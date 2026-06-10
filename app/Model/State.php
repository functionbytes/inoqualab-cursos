<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    protected $table = 'states';

    protected $fillable = [
        'title',
        'country_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $countrie)
    {
        return $query->where('id', $countrie)->first();
    }

    public function countrie(): BelongsTo
    {
        return $this->belongsTo('App\Model\Countrie', 'countrie_id', 'id');
    }
}
