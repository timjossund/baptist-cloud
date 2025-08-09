<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PostForm extends Component
{
    use withFileUploads;

    public function render()
    {
        $categories = Category::get();
        return view('livewire.post-form', ['categories' => $categories]);
    }

    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
    public $content = '';

    #[Validate('nullable')]
    public $tags = '';

    #[Validate('required')]
    public $category_id = 0;

    #[Validate('required|image|mimes:jpeg,png,jpg|max:6048')]
    public $image;

    public $ad_heading = '';
    public $ad_description = '';
    public $ad_link = '';

    public $user_id = 0;
    public $slug = '';

    public function store() {
        $this->validate();
        $this->title = strip_tags($this->title);
        $this->content = strip_tags($this->content);
        $this->tags = strip_tags($this->tags);
        $this->ad_heading = strip_tags($this->ad_heading);
        $this->ad_description = strip_tags($this->ad_description);
        $this->ad_link = strip_tags($this->ad_link);
        $category_id = $this->category_id;
        $featureImage = "image" . uniqid(10) . ".jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($this->image);
        $imgNew = $image->cover(1200, 400)->toJpeg();
        Storage::disk('public')->put("images/".$featureImage, $imgNew);
        $this->image = $featureImage;
        $this->slug = Str::slug($this->title . '-' . Str::random(3));

        $this->user_id = auth()->id();

        Post::create(
            $this->only(['title', 'content', 'tags', 'category_id', 'image', 'slug', 'user_id', 'ad_heading', 'ad_description', 'ad_link'])
        );
        return redirect('/post/'. $this->slug .'/edit')->with('success', 'Draft Saved');
    }
}
