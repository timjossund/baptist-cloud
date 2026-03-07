<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use App\Models\User;

class PublicProfileController extends Controller
{
    public function show(User $user)
    {
        $followersCount = $user->followers()->count();
        if (auth()->user()->id != $user->id) {
            $posts = $user->posts()->with('category')->withCount('likes')->whereNotNull('published_at')->latest('published_at')->cursorPaginate(5);
            $sermons = $user->sermons()->whereNotNull('created_at')->latest('created_at')->cursorPaginate(5);
        } else {
            $posts = $user->posts()->with('category')->withCount('likes')->latest()->cursorPaginate(5);
            $sermons = $user->sermons()->whereNotNull('created_at')->latest('created_at')->cursorPaginate(5);
        }
        $ads = BcAd::all();

        return view('public-profile', [
            'user' => $user, 'posts' => $posts, 'sermons' => $sermons, 'ads' => $ads, 'followersCount' => $followersCount,
        ]);
    }
}
