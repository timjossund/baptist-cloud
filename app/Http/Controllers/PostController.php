<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        \DB::listen( function ($query) {
//            \Log::info($query->sql);
//        });

        $user = auth()->user();
        $query = Post::latest();
        if ($user) {
            $ids = $user->following()->pluck('users.id');
            $query->whereIn('user_id', [$ids, $user->id]);
        }
        $posts = $query->simplePaginate(5);
        return view('home-page', ['posts' => $posts]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::get();
        return view('create-post', ['categories' => $categories]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:16048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'published_at' => ['nullable', 'datetime'],
        ]);


//        $image = $data['image'];
        //unset($data['image']);
        $data['slug'] = Str::slug($data['title']);

        $featureImage = "post-image" . $data['slug'] . ".jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($data['image']);
        $imgNew = $image->cover(1200, 400)->toJpeg();
        Storage::disk('public')->put("images/".$featureImage, $imgNew);

        $data['image'] = $featureImage;

        $data['user_id'] = auth()->id();

        Post::create($data);

        return redirect()->route('home-page');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username, Post $post)
    {
        return view('single-post', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('edit-post', ['post' => $post]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:16048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'published_at' => ['nullable', 'datetime'],
        ]);

        $post->update($data);

        return redirect()->route('home-page');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('home-page');
    }

    public function category(Category $category) {
        $post = $category->posts()->latest()->simplePaginate(5);
        return view('home-page', ['posts' => $post]);
    }
}
