<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $connection = 'mysql_second';

    protected $table = 'certifications';

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'thumbnail',
        'available',
        'description',
        'created_at',
        'updated_at',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

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

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }
}
