<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'answers';

    protected $fillable = ['instructor_id', 'ans_user_id', 'ques_user_id', 'course_id', 'question_id', 'answer', 'status'];

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'ans_user_id', 'id');
    }

    public function courses()
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo('App\Model\Question', 'question_id', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo('App\Model\User', 'instructor_id', 'id');
    }
}
