<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'currencies';

    protected $fillable = ['icon', 'currency', 'default'];
}
