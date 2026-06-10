<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Testimonie extends Model
{
    protected $table = 'testimonies';

    protected $fillable = [
        'slack',
        'firstname',
        'lastname',
        'description',
        'available',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1)->get();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }
}
