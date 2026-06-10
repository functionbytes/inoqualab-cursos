<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountItem extends Model
{
    use HasFactory;

    protected $table = 'account_items';

    protected $fillable = [
        'slack',
        'course_id',
        'account_id',
        'quantity',
        'usage',
        'created_at',
        'updated_at',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo('App\Models\Account\Account');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course');
    }
}
