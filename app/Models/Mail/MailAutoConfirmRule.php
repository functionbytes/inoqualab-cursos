<?php

namespace App\Models\Mail;

use App\Models\Enterprise\Enterprise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailAutoConfirmRule extends Model
{
    protected $table = 'mail_auto_confirm_rules';

    protected $fillable = [
        'enterprise_id',
        'min_confidence',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'min_confidence' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }
}
