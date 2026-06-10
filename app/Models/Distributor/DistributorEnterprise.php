<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorEnterprise extends Model
{
    use HasFactory;

    protected $table = 'distributor_enterprises';

    protected $fillable = [
        'enterprise_id',
        'distributor_id',
        'created_at',
        'updated_at',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id', 'id');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id', 'id');
    }
}
