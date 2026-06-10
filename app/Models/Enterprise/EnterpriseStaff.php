<?php

namespace App\Models\Enterprise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseStaff extends Model
{
    use HasFactory;

    protected $table = 'enterprise_staff';

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
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id', 'id');
    }
}
