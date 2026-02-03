<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sermon extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'audio',
        'video',
        'image',
        'published_at',
    ];
}
