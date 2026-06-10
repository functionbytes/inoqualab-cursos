<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'firstname',
        'lastname',
        'email',
        'cellphone',
        'message',
        'reviewed',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }
}
