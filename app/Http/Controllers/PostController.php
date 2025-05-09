<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $query = Post::with(['user', 'media'])->latest();
        if ($user) {
            $ids = $user->following()->pluck('users.id');
            $query->whereIn('user_id', $ids);
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
        unset($data['image']);
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['title']);

//        $imagePath = $image->store('posts', 'public');
//        $data['image'] = $imagePath;

        $post = Post::create($data);

        $post->addMediaFromRequest('image')->toMediaCollection();

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }

    public function category(Category $category) {
        $post = $category->posts()->latest()->simplePaginate(5);
        return view('home-page', ['posts' => $post]);
    }
}
