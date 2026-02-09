<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read User|null $user
 */
class Post extends Model
{
    // use HasFactory;

    protected $fillable = [
        'image',
        'title',
        'content',
        'tags',
        'slug',
        'category_id',
        'ad_heading',
        'ad_description',
        'ad_link',
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

    public function image(): Attribute
    {
        return Attribute::make(get: function ($value) {
            return $value ? 'https://s3.us-east-1.wasabisys.com/sermons/images/'.$value : '/default-avatar.png';
        });
    }

    public function readTime()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200);

        return $minutes;
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reporting::class);
    }
}
