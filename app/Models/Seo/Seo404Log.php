<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Seo404Log extends Model
{
    protected $table = 'seo_404_logs';

    protected $fillable = [
        'path',
        'referer',
        'user_agent',
        'ip',
        'hit_count',
        'has_redirect',
        'first_seen_at',
        'last_seen_at',
    ];

    public function casts(): array
    {
        return [
            'has_redirect' => 'boolean',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public static function recordHit(string $path, ?string $referer, ?string $ip, ?string $userAgent): void
    {
        try {
            $existing = static::where('path', $path)->first();

            if ($existing) {
                $existing->update([
                    'hit_count' => DB::raw('hit_count + 1'),
                    'last_seen_at' => now(),
                    'referer' => $referer,
                    'ip' => $ip,
                ]);
            } else {
                static::create([
                    'path' => $path,
                    'referer' => $referer,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'hit_count' => 1,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                ]);
            }
        } catch (\Throwable) {
            // No bloqueamos la respuesta si el log falla
        }
    }

    public function scopeWithoutRedirect($query)
    {
        return $query->where('has_redirect', false);
    }

    public function scopeOrderByHits($query)
    {
        return $query->orderByDesc('hit_count');
    }
}
