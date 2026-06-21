<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUid
{
    protected static function bootHasUid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
        });
    }
}
