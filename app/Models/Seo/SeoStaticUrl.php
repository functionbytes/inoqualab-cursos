<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;

class SeoStaticUrl extends Model
{
    protected $table = 'seo_static_urls';

    protected $fillable = [
        'url',
        'priority',
        'changefreq',
        'is_active',
        'notes',
    ];

    public const CHANGEFREQ_OPTIONS = [
        'always',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'never',
    ];

    public const PRIORITY_OPTIONS = [
        0.1,
        0.2,
        0.3,
        0.4,
        0.5,
        0.6,
        0.7,
        0.8,
        0.9,
        1.0,
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'decimal:1',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
