<?php

namespace App\Models\Mailer;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MailerLayout extends Model
{
    use HasUid;

    protected $table = 'mailer_layouts';

    protected $fillable = [
        'uid', 'name', 'alias', 'code', 'type', 'group_name',
        'subject', 'content', 'is_protected', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeAlias($query, $alias)
    {
        return $query->where('alias', $alias);
    }

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }
}
