<?php

namespace App\Models\Course;

use App\Http\Seo\HasSeo;
use App\Http\Sitemap\HasSitemapItems;
use App\Models\Concerns\HasFinders;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory,
        HasFinders, HasSeo, HasSitemapItems, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'courses';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

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
        'level',
        'rating',
        'promotion',
        'website',
        'available',
        'type',
        'exam',
        'certificate',
        'categorie_id',
        'certification_id',
        'certifier_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

    }

    public function getUrlAttribute(): string
    {
        return route('courses.view', $this->slack);
    }

    public function getSitemapPriorityAttribute(): string
    {
        return '0.9';
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlug($query, $slug)
    {
        $model = $query->where('slug', $slug)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
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

        $purchasedCourseIds = Order::where('condition_id', 1)->where('user_id', $user)->pluck('course_id');

        return Course::whereIn('id', $purchasedCourseIds)->get();

    }

    public function chapters(): HasMany
    {
        return $this->hasMany('App\Models\Course\CourseChapter', 'course_id')
            ->where('available', 1)
            ->orderBy('position', 'asc');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany('App\Models\Course\CourseLesson', 'course_id')
            ->where('available', 1)
            ->orderBy('position', 'asc');
    }

    public function order(): HasMany
    {
        return $this->hasMany('App\Models\Order\Order', 'course_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany('App\Models\Course\CourseAnnouncement', 'course_id', 'id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo('App\Models\Certifier', 'certifier_id', 'id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo('App\Models\Certification', 'certification_id', 'id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseCategorie', 'categorie_id', 'id');
    }

    public function quiztopic(): HasMany
    {
        return $this->hasMany('App\Models\Quiz\QuizTopic', 'course_id')->where('available', 1);
    }

    public function examtopic(): HasOne
    {
        return $this->hasOne('App\Models\Exam\ExamTopic', 'course_id')->where('available', 1);
    }

    public function bundles()
    {
        return $this->belongsToMany('App\Models\Bundle\Bundle', 'bundle_courses');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(CourseAlias::class, 'course_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany('App\Models\Course\CourseReview', 'course_id');
    }

    /**
     * Recalcula el rating del curso como el promedio de sus reseñas
     * y lo persiste en la columna `rating`.
     */
    public function recalculateRating(): void
    {
        $avg = $this->reviews()->avg('rating');
        $this->rating = $avg ? round($avg, 1) : 0;
        $this->save();
    }
}
