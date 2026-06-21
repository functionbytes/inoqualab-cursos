<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorStaff extends Model
{
    use HasFactory;

    protected $table = 'distributor_staff';

    protected $fillable = [
        'user_id',
        'distributor_id',
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
        return $query->where('available', 0);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id', 'id');
    }
}
