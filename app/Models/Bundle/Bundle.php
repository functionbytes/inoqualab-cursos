<?php

namespace App\Models\Bundle;

use App\Http\Seo\HasSeo;
use App\Http\Sitemap\HasSitemapItems;
use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Bundle extends Model implements HasMedia
{
    use HasFactory,
        HasFinders, HasSeo, HasSitemapItems, InteractsWithMedia, LogsActivity;

    protected $table = 'bundles';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'description',
        'price',
        'available',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'start_date',
        'expire_at',
    ];

    public function getUrlAttribute(): string
    {
        return route('bundles.view', $this->slug);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlug($query, $slug)
    {
        $model = $query->where('slug', $slug)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Course\Course');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany('App\Models\Course\CourseReview', 'reviewable');
    }

    public function item(): MorphMany
    {
        return $this->morphMany('App\Models\Order\OrderItem', 'item');
    }
}
