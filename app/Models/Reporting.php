<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporting extends Model
{
    protected $fillable = [
        'reporter_name',
        'reporter_email',
        'post_id',
        'post_title',
        'description',
        'post_slug',
        'username'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
