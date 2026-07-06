<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemarketingRun extends Model
{
    protected $fillable = [
        'command',
        'cohort_date',
        'found',
        'sent',
    ];

    protected function casts(): array
    {
        return [
            'cohort_date' => 'date',
            'found' => 'integer',
            'sent' => 'integer',
        ];
    }
}
