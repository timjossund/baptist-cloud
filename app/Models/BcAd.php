<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BcAd extends Model
{
    protected $fillable = [
        'title',
        'description',
        'link',
        'int'
    ];
}
