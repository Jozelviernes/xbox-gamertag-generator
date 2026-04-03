<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorWord extends Model
{
    protected $fillable = [
        'word',
        'language',
        'theme',
    ];
}