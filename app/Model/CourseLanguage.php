<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CourseLanguage extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'course_languages';

    protected $fillable = ['name', 'status'];

    public function courses()
    {
        return $this->hasMany('App\Model\Course', 'language_id');
    }
}
