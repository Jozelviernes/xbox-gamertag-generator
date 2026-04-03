<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorSuffix extends Model
{
    protected $fillable = [
        'word',
        'language',
    ];
}