<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'exam_questions';

    protected $fillable = [
        'slack',
        'course_id',
        'topic_id',
        'question',
        'a',
        'b',
        'c',
        'd',
        'answer',
        'available',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
