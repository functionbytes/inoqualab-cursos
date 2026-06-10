<?php

namespace App\Models\Bundle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class BundleCourse extends Model
{
    use HasFactory;

    protected $table = 'bundle_course';

    protected $fillable = [
        'bundle_id',
        'course_id',
    ];

    public function course(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Course\Course', 'App\Models\Bundle\Bundle');
    }

    public function bundle(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Course\Course', 'App\Models\Bundle\Bundle');
    }
}
