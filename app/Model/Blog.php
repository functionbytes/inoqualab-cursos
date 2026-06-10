<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Blog extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $connection = 'mysql_second';

    protected $table = 'blogs';

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

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function scopeCategories($query, $categorie)
    {
        return $query->where('categorie_id', $categorie);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Model\CategorieBlog', 'categorie_id', 'id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany('App\Model\Tag');
    }
}
