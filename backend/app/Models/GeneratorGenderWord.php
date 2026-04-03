<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorGenderWord extends Model
{
    protected $fillable = [
        'word',
        'language',
        'gender',
        'position',
    ];
}