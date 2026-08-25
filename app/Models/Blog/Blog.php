<?php

namespace App\Models\Blog;

use App\Http\Seo\HasSeo;
use App\Http\Sitemap\HasSitemapItems;
use App\Models\Concerns\HasCardImage;
use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Blog extends Model implements HasMedia
{
    use HasCardImage, HasFactory,
        HasFinders, HasSeo, HasSitemapItems, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'blogs';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'description',
        'content',
        'available',
        'date_at',
        'categorie_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Conversión optimizada para las tarjetas del blog: webp redimensionado
     * (cwebp disponible → optimización automática). Activar con
     * `php artisan media-library:regenerate` tras subir miniaturas.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->width(800)
            ->format('webp')
            ->quality(82)
            ->optimize()
            ->nonQueued()
            ->performOnCollections('thumbnail');
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function getUrlAttribute(): string
    {
        return route('blogs.view', $this->slug);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Models\Blog\BlogCategorie', 'categorie_id', 'id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Blog\BlogTag', 'blog_tag', 'blog_id', 'tag_id');

    }
}
