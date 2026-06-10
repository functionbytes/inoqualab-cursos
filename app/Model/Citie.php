<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

class Citie extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'cities';

    protected $fillable = [
        'title',
        'state_id',
        'created_at',
        'updated_at',
    ];

    public function state(): belongsTo
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function user(): belongsToMany
    {
        return $this->belongsToMany('App\Model\User');
    }
}
