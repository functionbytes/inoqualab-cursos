<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $connection = 'mysql_second';

    public $timestamps = false;

    protected $table = 'pages';

    protected $fillable = [
        'title', 'slug', 'details', 'status',
    ];
}
