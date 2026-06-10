<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enterprise extends Model
{
    protected $table = 'enterprises';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'title',
        'address',
        'cellphone',
        'nit',
        'leading',
        'supporting',
        'leading',
        'available',
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

    public function users(): HasMany
    {
        return $this->hasMany('App\Model\EnterpriseUser', 'enterprise_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany('App\Model\EnterpriseCourse', 'enterprise_id');
    }

    public function userss(): BelongsToMany
    {
        return $this->belongsToMany('App\Model\User', 'enterprise_user')->withPivot('enterprise_id');
    }

    public function coursess(): BelongsToMany
    {
        return $this->belongsToMany('App\Model\Course', 'enterprise_course')->withPivot('enterprise_id');
    }
}
