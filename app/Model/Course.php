<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $connection = 'mysql_second';

    protected $table = 'courses';

    protected $fillable = [
        'id',
        'slack',
        'title',
        'slug',
        'price',
        'discount',
        'day',
        'film',
        'short',
        'detail',
        'requirement',
        'who',
        'learn',
        'featured',
        'promotion',
        'website',
        'available',
        'type',
        'exam',
        'certificate',
        'categorie_id',
        'certification_id',
        'certifier_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }

    public function scopeOne($query, $id)
    {
        return $query->where('id', '=', $id);
    }

    public function scopeTwo($query, $id)
    {
        return $query->where('id', '<=', $id);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    public function scopeWebsite($query)
    {
        return $query->where('website', 1);
    }

    public static function scopeSearch($query, $searchTerm)
    {
        return $query->where('title', 'like', '%'.$searchTerm.'%');
    }

    public static function purchases($user)
    {

        $purchasedCourseIds = Order::where('condition_id', 1)
            ->where('user_id', $user)
            ->pluck('course_id');

        return Course::whereIn('id', $purchasedCourseIds)->get();

    }

    public function chapter(): HasMany
    {
        return $this->hasMany('App\Model\CourseChapter', 'course_id')->where('available', 1)->orderBy('position', 'asc');
    }

    public function class(): HasMany
    {
        return $this->hasMany('App\Model\CourseClass', 'course_id')->where('available', 1)->orderBy('position', 'asc');
    }

    public function order(): HasMany
    {
        return $this->hasMany('App\Model\Order', 'course_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany('App\Model\Announcement', 'course_id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo('App\Model\Certifier', 'certifier_id', 'id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo('App\Model\Certification', 'certification_id', 'id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Model\Categorie', 'categorie_id', 'id');
    }

    public function quiztopic(): HasMany
    {
        return $this->hasMany('App\Model\QuizTopic', 'course_id')->where('available', 1);
    }

    public function examtopic(): HasOne
    {
        return $this->hasOne('App\Model\ExamTopic', 'course_id')->where('available', 1);
    }
}
