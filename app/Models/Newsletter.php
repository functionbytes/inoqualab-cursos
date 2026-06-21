<?php

namespace App\Models;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'newsletters';

    protected $fillable = [
        'slack',
        'user_id',
        'email',
        'name',
        'source',
        'is_active',
        'ip_address',
        'confirmation_token',
        'subscribed_at',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscribed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    public function subscribe(): void
    {
        $this->update([
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public function scopeSubscribed(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeValidate($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }
}
