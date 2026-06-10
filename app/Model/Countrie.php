<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Countrie extends Model
{
    protected $table = 'countries';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
    ];
}
