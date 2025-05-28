<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditPost extends Component
{
    public function render(Post $post)
    {
        $categories = Category::get();
        return view('livewire.edit-post', [
            'post' => $post, 'categories' => $categories
        ]);
    }

    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
    public $content = '';

    #[Validate('required')]
    public $category_id = 0;

    #[Validate('required|image|mimes:jpeg,png,jpg|max:6048')]
    public $image;

    public $user_id = 0;
    public $slug = '';

    public function update()
    {
        $this->title = strip_tags($this->title);
        //$this->content = strip_tags($this->content);
        $category_id = $this->category_id;
        if ($this->image) {
        $featureImage = "image" . uniqid(10) . ".jpg";
        $manager = new ImageManager(new Driver());
        $image = $manager->read($this->image);
        $imgNew = $image->cover(1200, 675)->toJpeg();
        Storage::disk('public')->put("images/".$featureImage, $imgNew);
        $this->image = $featureImage;
        $this->slug = Str::slug($this->title . '-' . Str::random(3));
        }
        $this->user_id = auth()->id();
        $this->validate();

        $this->post->update(
            $this->all()
        );
    }
}
