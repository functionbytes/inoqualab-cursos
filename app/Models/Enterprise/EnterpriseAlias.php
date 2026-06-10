<?php

namespace App\Models\Enterprise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseAlias extends Model
{
    use HasFactory;

    protected $table = 'enterprise_aliases';

    const TYPE_CODE = 'code';

    const TYPE_NAME = 'name';

    protected $fillable = [
        'enterprise_id',
        'alias_type',
        'alias_value',
        'normalized_value',
        'created_at',
        'updated_at',
    ];

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }
}
