<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Setting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'settings';

    protected $fillable = ['logo', 'favicon', 'paytm_enable', 'project_title', 'promo_text'];

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }
}
