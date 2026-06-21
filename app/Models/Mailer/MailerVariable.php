<?php

namespace App\Models\Mailer;

use App\Traits\HasUid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MailerVariable extends Model
{
    use HasUid;

    protected $table = 'mailer_variables';

    protected $fillable = [
        'uid', 'key', 'name', 'description', 'example_value',
        'category', 'module', 'is_system', 'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
