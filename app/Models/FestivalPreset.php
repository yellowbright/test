<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestivalPreset extends Model
{
    protected $fillable = [
        'category',
        'name',
        'month',
        'day',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
