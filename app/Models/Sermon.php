<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Sermon extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'audio_url',
        'video_url',
        'image_url',
        'created_at',
        'updated_at',
    ];

//    public function audio(): Attribute {
//        return Attribute::make(get: function($value) {
//            $str = 'https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/';
//            return $value ? '' . $str . '' . $value : '';
//        });
//    }
}
