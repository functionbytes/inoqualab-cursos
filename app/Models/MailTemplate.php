<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = ['key', 'name', 'subject', 'content', 'description', 'variables'];

    protected $casts = ['variables' => 'array'];

    public static function resolve(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
