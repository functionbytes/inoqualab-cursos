<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseUser extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'enterprise_user';

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'available',
        'created_at',
        'updated_at',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function scopeDisabled($query)
    {
        return $query->where('available', 1);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Model\Enterprise', 'enterprise_id', 'id');
    }
}
