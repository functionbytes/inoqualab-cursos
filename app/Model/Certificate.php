<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certificate extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $connection = 'mysql_second';

    protected $table = 'certificates';

    protected $fillable = [
        'slack',
        'user_id',
        'course_id',
        'order_id',
        'exam_id',
        'certifier_id',
        'certification_id',
        'start_at',
        'end_at',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo('App\Model\Exam', 'exam_id', 'id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo('App\Model\Certifier', 'certifier_id', 'id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo('App\Model\Certification', 'certification_id', 'id');
    }
}
