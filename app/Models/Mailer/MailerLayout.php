<?php

namespace App\Models\Mailer;

use App\Services\Mailer\MailerTemplateRendererService;
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

    protected static function booted(): void
    {
        // MailerTemplateRendererService cachea header/footer/wrapper 1h por alias
        // (getCachedLayoutContent). Sin esto, guardar un cambio aquí -- desde el
        // panel, tinker o un seeder -- lo deja invisible en los correos reales
        // hasta que el cache expire solo.
        static::saved(fn () => MailerTemplateRendererService::clearCache());
        static::deleted(fn () => MailerTemplateRendererService::clearCache());
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
