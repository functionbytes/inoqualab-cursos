<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $connection = 'mysql_second';

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
        'role',
        'company',
        'detail',
        'user_img',
        'citie_id',
        'enterprise_id',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('users.available', 1);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Model\Countrie', 'country_id', 'id');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Model\Enterprise', 'enterprise_id', 'id');
    }

    public function relation(): HasOne
    {
        return $this->hasOne('App\Model\EnterpriseUser', 'user_id', 'id');
    }

    public function relations(): HasOneThrough
    {
        return $this->HasOneThrough('App\Model\Enterprise', 'App\Models\EnterpriseUser', 'user_id', 'id', 'id', 'enterprise_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany('App\Model\Course', 'user_id');
    }

    public function answer(): HasMany
    {
        return $this->hasMany('App\Model\Question', 'user_id');
    }

    public function announsment(): HasMany
    {
        return $this->hasMany('App\Model\Announcement', 'user_id');
    }

    public function review(): HasMany
    {
        return $this->hasMany('App\Model\ReviewRating', 'user_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany('App\Model\Blog', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Model\Order', 'user_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany('App\Model\Certificate', 'user_id');
    }

    public static function auth()
    {

        return Auth::user();
    }

    public static function validateIdentification($identification)
    {

        $existenceUser = User::where('identification', $identification)->get();

        if ($existenceUser->count() == 1) {
            return $existenceUser;
        } else {
            return null;
        }
    }

    public static function validations($email, $identification)
    {

        $existenceUser = User::where('email', $email)->orWhere('identification', $identification)->get();

        if ($existenceUser->count() == 1) {
            return $existenceUser;
        } else {
            return null;
        }
    }

    public static function validate($email)
    {

        $existenceUser = User::where('email', $email)->get();

        if ($existenceUser->count() == 1) {
            return $existenceUser;
        } else {
            return null;
        }
    }

    public function scopeValidates($query)
    {
        return $query->where('validation', 0)->get();
    }

    public function scopeValidationsEmail($query, $email)
    {
        return $query->where('email', $email)->get();
    }

    public function scopeValidationEmail($query, $email)
    {
        return $query->where('email', $email)->first();
    }

    public function scopeValidations($query)
    {
        return $query->where('slack', null)->get();
    }

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeIdentifications($query, $identification)
    {
        return $query->where('identification', $identification)->get();
    }

    public function scopeIdentification($query, $identification)
    {
        return $query->where('identification', $identification)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }

    public function scopeEmail($query, $email)
    {
        return $query->where('email', $email)->first();
    }

    public function scopeRoles($query, $role)
    {
        return $query->where('role', $role)->get();
    }

    public static function existence($slack)
    {
        return User::where('slack', '=', $slack)->first();
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
