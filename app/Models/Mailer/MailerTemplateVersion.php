<?php

namespace App\Models\Mailer;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailerTemplateVersion extends Model
{
    protected $table = 'mailer_template_versions';

    protected $fillable = [
        'mailer_template_id', 'created_by', 'subject', 'content', 'change_note',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MailerTemplate::class, 'mailer_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
