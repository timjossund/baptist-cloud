<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPostImage;
use App\Models\BcAd;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = Post::query()->whereNotNull('published_at')->latest('published_at');
        $ads = BcAd::all();

        if ($user) {
            $ids = $user->following()->pluck('users.id');
            $query->whereIn('user_id', $ids);
        }

        $posts = $query->with('user', 'category')->withCount('likes')->cursorPaginate(5);

        return view('home-page', ['posts' => $posts, 'ads' => $ads]);

    }

    public function indexHome()
    {
        return redirect('/');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:6048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id', 'integer'],
            'content' => 'required',
            'tags' => ['nullable', 'max:255'],
            'ad_heading' => 'nullable',
            'ad_description' => 'nullable',
            'ad_link' => 'nullable',
            'published_at' => ['nullable', 'date'],
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['content'] = strip_tags($data['content']);
        $data['category_id'] = (int) $data['category_id'];
        if (isset($data['tags'])) {
            $data['tags'] = strip_tags($data['tags']);
        }
        if (isset($data['ad_heading'])) {
            $data['ad_heading'] = strip_tags($data['ad_heading']);
        }
        if (isset($data['ad_description'])) {
            $data['ad_description'] = strip_tags($data['ad_description']);
        }
        if (isset($data['ad_link'])) {
            $data['ad_link'] = strip_tags($data['ad_link']);
        }

        $data['slug'] = Str::slug($data['title'].'-'.Str::random(3));
        $data['user_id'] = auth()->id();

        $tempImagePath = $request->file('image')->store('temp-post-images', 'local');
        unset($data['image']);

        $post = Post::create($data);

        ProcessPostImage::dispatch($post, $tempImagePath);

        if (! app()->runningUnitTests()) {
            sleep(3);
        }

        return redirect('/post/'.$data['slug'].'/edit')->with('success', 'Draft Saved');
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
     * Display the specified resource.
     */
    public function show(string $username, Post $post)
    {
        $post['content'] = Str::markdown($post->content);
        $ads = BcAd::all();
        $maxAd = $ads->max('int');

        return view('single-post', ['post' => $post, 'ads' => $ads, 'maxAd' => $maxAd]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        if ($post->user_id != auth()->id() && ! auth()->user()->is_admin) {
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
        if ($post->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:6048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'tags' => 'nullable',
            'ad_heading' => 'nullable',
            'ad_description' => 'nullable',
            'ad_link' => 'nullable',
            'published_at' => ['nullable', 'timestamp'],
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['content'] = strip_tags($data['content']);
        $data['category_id'] = strip_tags($data['category_id']);
        $data['tags'] = strip_tags($data['tags']);
        if (isset($data['ad_heading'])) {
            $data['ad_heading'] = strip_tags($data['ad_heading']);
        }
        if (isset($data['ad_description'])) {
            $data['ad_description'] = strip_tags($data['ad_description']);
        }
        if (isset($data['ad_link'])) {
            $data['ad_link'] = strip_tags($data['ad_link']);
        }
        $data['slug'] = $post->getRawOriginal('slug');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $oldImage = $post->getRawOriginal('image');
            $tempImagePath = $request->file('image')->store('temp-post-images', 'local');
            unset($data['image']);

            $post->fill($data);
            $post->save();

            ProcessPostImage::dispatch($post, $tempImagePath, $oldImage);
        } else {
            unset($data['image']);
            $post->fill($data);
            $post->save();
        }

        return redirect('/@'.auth()->user()->username)->with('success', 'Draft Saved');
    }

    public function publish(Request $request, Post $post)
    {
        if ($post->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:6048',
            'title' => 'required|max:255',
            'category_id' => ['required', 'exists:categories,id'],
            'content' => 'required',
            'tags' => 'nullable',
            'ad_heading' => 'nullable',
            'ad_description' => 'nullable',
            'ad_link' => 'nullable',
            'published_at' => ['nullable', 'timestamp'],
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['category_id'] = strip_tags($data['category_id']);
        $data['tags'] = strip_tags($data['tags']);
        if (isset($data['ad_heading'])) {
            $data['ad_heading'] = strip_tags($data['ad_heading']);
        }
        if (isset($data['ad_description'])) {
            $data['ad_description'] = strip_tags($data['ad_description']);
        }
        if (isset($data['ad_link'])) {
            $data['ad_link'] = strip_tags($data['ad_link']);
        }
        $data['slug'] = $post->getRawOriginal('slug');
        $data['user_id'] = auth()->id();
        $data['published_at'] = now();

        if ($request->hasFile('image')) {
            $oldImage = $post->getRawOriginal('image');
            $tempImagePath = $request->file('image')->store('temp-post-images', 'local');
            unset($data['image']);

            $post->fill($data);
            $post->save();

            ProcessPostImage::dispatch($post, $tempImagePath, $oldImage);
        } else {
            unset($data['image']);
            $post->fill($data);
            $post->save();
        }

        return redirect('/@'.auth()->user()->username)->with('success', 'Post Published');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
        $postImage = $post->getRawOriginal('image');
        Storage::disk('postImages')->delete('post-images/'.$postImage);
        $post->delete();

        return redirect('/@'.auth()->user()->username)->with('success', 'Post Deleted');
    }

    public function category(Category $category)
    {
        $posts = $category->posts()->withCount('likes')->whereNotNull('published_at')->latest('published_at')->cursorPaginate(5);
        $ads = BcAd::all();

        return view('home-page', ['posts' => $posts, 'ads' => $ads]);
    }

    public function searchAuthor(User $user)
    {
        $users = User::query()->cursorPaginate(5);

        return view('search-authors', ['users' => $users]);
    }

    public function searchPost()
    {
        return view('home-page');
    }

    public function markdownSandbox()
    {
        return view('learn-markdown');
    }

    public function search()
    {
        return view('search');
    }
}
