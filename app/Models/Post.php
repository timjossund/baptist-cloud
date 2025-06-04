<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //use HasFactory;

    protected $fillable = [
        'image',
        'title',
        'content',
        'tags',
        'slug',
        'category_id',
        'user_id',
        'published_at',
    ];

//    public function registerMediaConversions(?Media $media = null): void
//    {
//        $this
//            ->addMediaConversion('preview')
//            ->width(250);
//        $this
//            ->addMediaConversion('large')
//            ->width(1200);
//    }

    public function image(): Attribute {
        return Attribute::make(get: function($value) {
            return $value ? '/storage/images/' . $value : '/default-avatar.png';
        });
    }

    public function readTime() {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200);
        return $minutes;
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }
}
