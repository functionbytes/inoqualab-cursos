<?php

namespace App\Model;

use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BlogTag extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'blog_tag';

    protected $fillable = [
        'blog_id',
        'tag_id',
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function scopeLastMonth($query, $limit = 5)
    {
        return $query->whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()])
            ->latest()
            ->limit($limit);
    }

    public function scopeLastWeek($query)
    {
        return $query->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()])
            ->latest();
    }

    public function scopeMonthly($query)
    {
        return $query->whereMonth('created_at', '=', date('m'));
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'id');
    }
}
