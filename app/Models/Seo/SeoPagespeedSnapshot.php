<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeoPagespeedSnapshot extends Model
{
    protected $table = 'seo_pagespeed_snapshots';

    protected $fillable = [
        'url',
        'url_path',
        'strategy',
        'performance',
        'accessibility',
        'best_practices',
        'seo',
        'lcp_ms',
        'inp_ms',
        'cls',
        'fcp_ms',
        'ttfb_ms',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'performance' => 'integer',
            'accessibility' => 'integer',
            'best_practices' => 'integer',
            'seo' => 'integer',
            'lcp_ms' => 'float',
            'inp_ms' => 'float',
            'cls' => 'float',
            'fcp_ms' => 'float',
            'ttfb_ms' => 'float',
            'captured_at' => 'datetime',
        ];
    }

    public function scopeForUrlPath(Builder $query, string $path): Builder
    {
        return $query->where('url_path', $path);
    }

    public function scopeForStrategy(Builder $query, string $strategy): Builder
    {
        return $query->where('strategy', $strategy);
    }
}
