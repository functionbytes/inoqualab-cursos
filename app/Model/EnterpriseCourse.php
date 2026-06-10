<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseCourse extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'enterprise_course';

    protected $fillable = [
        'course_id',
        'enterprise_id',
        'created_at',
        'updated_at',
    ];

    public function scopeValidate($query, $enterprise, $course)
    {
        return $query->where('course_id', $course)->where('enterprise_id', $enterprise)->first();
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Model\Enterprise', 'enterprise_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }
}
