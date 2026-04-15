<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glossary extends Model
{
    protected $fillable = [
        'term',
        'slug',
        'definition',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}