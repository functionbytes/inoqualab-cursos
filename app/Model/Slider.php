<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'sliders';

    protected $fillable = [
        'slack',
        'title',
        'subtitle',
        'description',
        'image',
        'available',
        'position',
        'ubication',
        'url',
        'created_at',
        'updated_at',
    ];

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }
}
