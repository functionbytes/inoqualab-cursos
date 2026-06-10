<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseCategorie extends Model
{
    protected $table = 'course_chapters';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'title',
        'description',
        'position',
        'available',
        'course_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function class(): HasMany
    {
        return $this->hasMany('App\Model\CourseClass', 'chapter_id')->where('available', 1)->orderBy('position');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }
}
