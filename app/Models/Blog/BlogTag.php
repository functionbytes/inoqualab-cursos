<?php

namespace App\Models\Blog;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogTag extends Model
{
    use HasFinders, SoftDeletes;

    protected $table = 'blog_tags';

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'available',
        'created_at',
        'updated_at',
    ];

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

    public function scopeSlug($query, $slug)
    {
        $model = $query->where('slug', $slug)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function blogs(): BelongsToMany
    {
        // Sin declarar tabla y claves, Laravel infería `blog_blog_tag` con
        // `blog_tag_id`: la tabla real es `blog_tag` con `tag_id`, así que la
        // relación nunca funcionó (rompía también el filtro por etiqueta del
        // listado). Es el espejo de Blog::tags(), que sí estaba bien puesta.
        // Sin withTimestamps(): la tabla pivote solo tiene id, tag_id y blog_id.
        return $this->belongsToMany('App\Models\Blog\Blog', 'blog_tag', 'tag_id', 'blog_id');
    }
}
