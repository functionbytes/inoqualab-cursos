<?php

namespace App\Models\Group;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'groups';

    protected $fillable = [
        'slack',
        'title',
        'slug',
        'available',
        'created_at',
        'updated_at',
    ];

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

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'groups_users', 'group_id', 'user_id');
    }

    public function user()
    {
        return $this->hasMany('App\Models\Group\GroupUser', 'group_id');
    }

    public function groupsuser()
    {
        return $this->user();
    }

    public function categorie()
    {
        return $this->hasMany('App\Models\Group\GroupCategorie', 'group_id');
    }
}
