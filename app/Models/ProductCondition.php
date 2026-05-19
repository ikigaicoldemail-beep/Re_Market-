<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rank',
        'quality_score',
        'color',
    ];

    protected $casts = [
        'quality_score' => 'integer',
        'rank' => 'integer',
    ];
}
