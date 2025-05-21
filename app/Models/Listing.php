<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'position',
        'church',
        'city',
        'state',
        'content',
        'phone',
        'email',
        'facebook',
        'website',
        'published_at',
    ];
}
