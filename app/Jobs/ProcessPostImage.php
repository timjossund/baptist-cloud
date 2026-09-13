<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProcessPostImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
        public string $tempImagePath,
        public ?string $oldImage = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! Storage::disk('local')->exists($this->tempImagePath)) {
            return;
        }

        $fullTempPath = Storage::disk('local')->path($this->tempImagePath);

        $featureImage = 'post-image-'.$this->post->slug.'-'.Str::random(3).'.jpg';

        $manager = new ImageManager(new Driver);
        $image = $manager->read($fullTempPath);
        $imgNew = $image->cover(1200, 400)->toJpeg();

        Storage::disk('postImages')->put('post-images/'.$featureImage, (string) $imgNew);

        $this->post->update([
            'image' => $featureImage,
        ]);

        Storage::disk('local')->delete($this->tempImagePath);

        if ($this->oldImage) {
            Storage::disk('postImages')->delete('post-images/'.$this->oldImage);
        }
    }
}
