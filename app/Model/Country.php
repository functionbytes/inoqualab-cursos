<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'mysql_second';

    protected $fillable = [
        'iso', 'name', 'nicename', 'iso3', 'numcode',
    ];

    public function states()
    {
        return $this->hasMany('App\Model\State', 'country_id');
    }

    public function city()
    {
        return $this->hasMany('App\Model\City', 'country_id');
    }
}
