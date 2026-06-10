<?php

namespace App\Models\Template;

use Acelle\Library\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    use HasUid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The template that belong to the categories.
     */
    public function templates()
    {
        return $this->belongsToMany('Acelle\Model\Template', 'templates_categories', 'category_id', 'template_id');
    }
}
