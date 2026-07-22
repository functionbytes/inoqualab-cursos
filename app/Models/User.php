<?php

namespace App\Models;

use App\Models\Bundle\Bundle;
use App\Models\Concerns\HasFinders;
use App\Models\Course\Course;
use App\Models\Invoice\Invoice;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory, HasFinders, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    // 'role' se asigna siempre por propiedad directa ($user->role = ...), nunca
    // por mass assignment: mantenerlo fuera de $fillable cierra la vía de
    // escalada de privilegios si algún endpoint futuro hiciera create/update($request->all()).
    protected $fillable = [
        'slack',
        'firstname',
        'lastname',
        'identification',
        'cellphone',
        'email',
        'password',
        'address',
        'available',
        'verified',
        'terms',
        'validation',
        'page',
        'setting',
        'company',
        'detail',
        'user_img',
        'citie_id',
        'enterprise_id',
        'email_verified_at',
        'remember_token',
        'timezone',
        'voilated',
        'last_login_at',
        'last_login_ip',
        'last_logins_at',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['full_name', 'image'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'confirmed' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

    }

    public function purchasedCourses()
    {
        $orders = Order::where('status', '=', 1)
            ->where('order_type', '=', 0)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $courses_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', "App\Models\Course\Course")
            ->pluck('item_id');
        $courses = Course::whereIn('id', $courses_id)
            ->get();

        return $courses;
    }

    public function scopeActiveUsersWithRole($query, $role)
    {
        return $query->where('role', $role)->where('available', 1);
    }

    public function purchasedBundles()
    {
        $orders = Order::where('status', '=', 1)
            ->where('order_type', '=', 0)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $bundles_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', Bundle::class)
            ->pluck('item_id');
        $bundles = Bundle::whereIn('id', $bundles_id)
            ->get();

        return $bundles;
    }

    public function findForPassport($user)
    {
        $user = $this->where('email', $user)->first();
        if (! $user) {
            return null;
        }
        if ($user->hasRole('student')) {
            return $user;
        }
    }

    public function scopeAvailable($query)
    {
        return $query->where('users.available', 1);
    }

    public function pendingOrders()
    {
        $orders = Order::where('status', '=', 0)
            ->where('user_id', '=', $this->id)
            ->get();

        return $orders;
    }

    public function subscribedBundles()
    {
        $orders = Order::where('order_type', '=', 1)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $bundles_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', Bundle::class)
            ->pluck('item_id');

        return Bundle::whereHas('bundleUser', function ($q) {
            $q->whereDate('expire_at', '>=', Carbon::now());
        })->whereIn('id', $bundles_id)->get();
    }

    public function getSubscribedCoursesIds()
    {
        $courseIds = $this->subscribedCourse()->pluck('id')->toArray();
        if ($this->subscribedBundles()->count()) {
            foreach ($this->subscribedBundles() as $bundle) {
                $courseIds = array_merge($courseIds, $bundle->courses()->pluck('id')->toArray());
            }
        }

        return $courseIds;
    }

    public function getPurchasedCoursesIds()
    {
        $courseIds = $this->purchasedCourses()->pluck('id')->toArray();
        if ($this->purchasedBundles()->count()) {
            foreach ($this->purchasedBundles() as $bundle) {
                $courseIds = array_merge($courseIds, $bundle->courses()->pluck('id')->toArray());
            }
        }

        return $courseIds;
    }

    public function subscribedCourse()
    {
        $orders = Order::where('order_type', '=', 1)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $courses_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', Course::class)
            ->pluck('item_id');

        return Course::whereHas('courseUser', function ($q) {
            $q->whereDate('expire_at', '>=', Carbon::now());
        })->whereIn('id', $courses_id)->get();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function threads()
    {
        return $this->belongsToMany(
            config('chatmessenger.thread_model'),
            'chat_participants',
            'user_id',
            'thread_id'
        )->withPivot('last_read');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id', 'id');
    }

    public function relation(): HasOne
    {
        return $this->hasOne('App\Models\Enterprise\EnterpriseUser', 'user_id', 'id');
    }

    public function getEnterpriseEmailAttribute()
    {
        if ($this->relation && $this->relation->enterprise) {
            return $this->relation->enterprise->email;
        }

        return null;
    }

    public function getEnterprise()
    {
        if ($this->relation && $this->relation->enterprise) {
            return $this->relation->enterprise;
        }

        return null;
    }

    public function relations(): HasOneThrough
    {
        return $this->HasOneThrough('App\Models\Enterprise\Enterprise', 'App\Models\Enterprise\EnterpriseUser', 'user_id', 'id', 'id', 'enterprise_id');
    }

    public function relationsEnterprises(): HasOneThrough
    {
        return $this->HasOneThrough('App\Models\Enterprise\Enterprise', 'App\Models\Enterprise\EnterpriseStaff', 'user_id', 'id', 'id', 'enterprise_id');
    }

    public function relationsEnterprise(): HasOne
    {
        return $this->hasOne('App\Models\Enterprise\EnterpriseStaff', 'user_id');
    }

    public function relationsDistributor(): HasOneThrough
    {
        return $this->HasOneThrough('App\Models\Distributor\Distributor', 'App\Models\Distributor\DistributorStaff', 'user_id', 'id', 'id', 'distributor_id');
    }

    public function relationDistributor(): HasOne
    {
        return $this->hasOne('App\Models\Distributor\DistributorStaff', 'user_id');
    }

    public function bundles()
    {
        return $this->hasMany(Bundle::class);
    }

    public function session(): HasOne
    {
        return $this->hasOne('App\Models\Setting\Session', 'user_id');
    }

    public function redirect()
    {

        switch ($this->role) {
            case 'superadmin' :
            case 'manager' :
                return 'manager.dashboard';
                break;
            case 'customer' :
                return 'customers.dashboard';
                break;
            case 'accounting' :
                return 'accounting.dashboard';
            case 'enterprise' :
                return 'enterprise.dashboard';
                break;
            case 'support' :
                return 'support.dashboard';
                break;
            case 'distributor' :
                return 'distributor.dashboard';
                break;
            default:
                return 'login';
        }
    }

    public function type()
    {

        switch ($this->role) {
            case 'manager':
                return 'Administrador';
                break;
            case 'customer':
                return 'Cliente';
                break;
            case 'enterprise':
                return 'Empresa';
                break;
            case 'support':
                return 'Suporte';
            case 'accounting':
                return 'Contabilidad';
                break;
            case 'distributor':
                return 'Distribuidor';
                break;
            default:
                return '';
        }
    }

    public function passwordHistories(): HasMany
    {
        return $this->hasMany('App\Models\Setting\PasswordHistory', 'user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany('App\Models\Setting\Session', 'user_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany('App\Models\Course\Course', 'user_id');
    }

    public function review(): HasMany
    {
        return $this->hasMany('App\Models\ReviewRating', 'user_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany('App\Models\Blog\Blog', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Models\Order\Order', 'user_id', 'id');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany('App\Models\Inscription', 'user_id')
            ->orderByRaw('CASE WHEN enroll_culminated IS NULL THEN 0 ELSE 1 END')
            ->orderBy('enroll_culminated', 'desc') // Ordena por enroll_culminated si no es NULL
            ->orderBy('enroll_start', 'desc'); // Ordena por fecha de creación si enroll_culminated es NULL

    }

    public function certificates(): HasMany
    {
        return $this->hasMany('App\Models\Users\Certificate', 'user_id');
    }

    public static function auth()
    {

        return Auth::user();
    }

    public function scopeValidates($query)
    {
        return $query->where('validation', 0);
    }

    public function scopeValidationsEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeValidationEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeValidations($query)
    {
        return $query->where('slack', null);
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeIdentifications($query, $identification)
    {
        return $query->where('identification', $identification);
    }

    public function scopeIdentification($query, $identification)
    {
        $model = $query->where('identification', $identification)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeRoles($query, $role)
    {
        return $query->where('role', $role);
    }

    public static function existence($slack)
    {
        return User::where('slack', '=', $slack)->first();
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->firstname.' '.$this->lastname),
        );
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_img,
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }
}
