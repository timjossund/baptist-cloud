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
    //    \DB::listen( function ($query) {
    //        \Log::info($query->sql);
    //    });

        $user = auth()->user();
        $query = Post::query()->latest();
        if ($user) {
            $ids = $user->following()->pluck('users.id');
            $query->whereIn('user_id', $ids);
        }
        $posts = $query->simplePaginate(5);


        return view('home-page', ['posts' => $posts]);

    }

    public function indexHome()
    {
        return redirect('/');
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
            'image' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'published_at' => ['nullable', 'datetime'],
        ]);

        $data['title'] = strip_tags($data['title']);
        //$data['content'] = strip_tags($data['content']);
        $data['category_id'] = strip_tags($data['category_id']);

//        $image = $data['image'];
        //unset($data['image']);
        $data['slug'] = Str::slug($data['title'] . '-' . Str::random(5));

        $featureImage = "image" . $data['slug'] . ".jpg";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($data['image']);
        $imgNew = $image->cover(1200, 400)->toJpeg();
        Storage::disk('public')->put("images/".$featureImage, $imgNew);

        $data['image'] = $featureImage;

        $data['user_id'] = auth()->id();

        Post::create($data);

        return redirect('/@'.auth()->user()->username);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username, Post $post)
    {
        $post['content'] = Str::markdown($post->content);
        return view('single-post', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        if ($post->user_id != auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }
        $categories = Category::get();
        return view('edit-post', ['post' => $post, 'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id != auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:6048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'published_at' => ['nullable', 'datetime'],
        ]);
        //dd($data);
        $data['title'] = strip_tags($data['title']);
//        $data['content'] = strip_tags($data['content']);
        $data['category_id'] = strip_tags($data['category_id']);
        $data['slug'] = Str::slug($data['title']);

        if ($request->file('image') == null) {
            $data['image'] = $post->getRawOriginal('image');
        } else {
            $oldImage = $post->getRawOriginal('image');
            $featureImage = "post-image" . $data['slug'] . ".jpg";

            $manager = new ImageManager(new Driver());
            $image = $manager->read($data['image']);
            $imgNew = $image->cover(1200, 400)->toJpeg();
            Storage::disk('public')->put("images/".$featureImage, $imgNew);
            Storage::disk('public')->delete("images/".$oldImage);

            $data['image'] = $featureImage;
        }


        $data['user_id'] = auth()->id();

        $post->fill($data);
        $post->save();

        return redirect('/@'.auth()->user()->username);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id != auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }
        $post->delete();
        return redirect('/@'.auth()->user()->username);
    }

    public function category(Category $category) {
        $post = $category->posts()->latest()->simplePaginate(5);
        return view('home-page', ['posts' => $post]);
    }
}
