<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Attributes\Rule;
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

    #[Rule('required')]
    public $title = '';

    #[Rule('required')]
    public $content = '';

    #[Rule('required')]
    public $category_id = 0;

    #[Rule('required|image|mimes:jpeg,png,jpg')]
    public $image;

    public $user_id = 0;
    public $slug = '';

    public function store() {
        $this->title = strip_tags($this->title);
        //$this->content = strip_tags($this->content);
        $category_id = $this->category_id;
        $featureImage = "image" . uniqid(10) . ".jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($this->image);
        $imgNew = $image->cover(1200, 675)->toJpeg();
        Storage::disk('public')->put("images/".$featureImage, $imgNew);
        $this->image = $featureImage;
        $this->slug = Str::slug($this->title . '-' . Str::random(3));

        $this->user_id = auth()->id();

        Post::create(
            $this->only(['title', 'content', 'category_id', 'image', 'slug', 'user_id'])
        );
        //dd($this->slug);
        return redirect('/post/'. $this->slug .'/edit')->with('success', 'Draft Saved Successfully');
    }
}
