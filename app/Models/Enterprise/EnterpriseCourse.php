<?php

namespace App\Models\Enterprise;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseCourse extends Model
{
    use HasFactory;

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
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }
}
