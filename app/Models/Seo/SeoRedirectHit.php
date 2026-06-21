<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SeoRedirectHit extends Model
{
    public $timestamps = false;

    protected $table = 'seo_redirect_hits';

    protected $fillable = [
        'seo_redirect_id',
        'hit_date',
        'hit_count',
    ];

    public function casts(): array
    {
        return [
            'hit_date' => 'date',
            'hit_count' => 'integer',
        ];
    }

    public function redirect(): BelongsTo
    {
        return $this->belongsTo(SeoRedirect::class, 'seo_redirect_id');
    }

    public static function recordHit(int $redirectId): void
    {
        static::query()->upsert(
            [
                'seo_redirect_id' => $redirectId,
                'hit_date' => now()->toDateString(),
                'hit_count' => 1,
            ],
            ['seo_redirect_id', 'hit_date'],
            ['hit_count' => DB::raw('hit_count + 1')]
        );
    }
}
