<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsletterCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid', 'name', 'subject', 'preheader', 'content',
        'status', 'recipients_count', 'sent_count', 'failed_count',
        'started_at', 'sent_at', 'created_by', 'newsletter_list_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Lista destino (null = enviar a todos los suscriptores). */
    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'newsletter_list_id');
    }
}
