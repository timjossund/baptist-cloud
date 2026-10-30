<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sermon extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'series_title',
        'audio_url',
        'video_url',
        'image_url',
        'user_id',
        'tags',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The audio file's path on the sermons disk, worked out from the stored URL.
     */
    public function audioPath(): ?string
    {
        $name = $this->audio_url ? basename((string) parse_url($this->audio_url, PHP_URL_PATH)) : '';

        return $name !== '' ? 'sermons/'.$name : null;
    }
}
