<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(User $user)
    {
        $followersCount = $user->followers()->count();
        if (auth()->user()->id != $user->id) {
            $posts = $user->posts()->with('category')->withCount('likes')->whereNotNull('published_at')->latest('published_at')->cursorPaginate(5);
        } else {
            $posts = $user->posts()->with('category')->withCount('likes')->latest()->cursorPaginate(5);
        }
        $ads = BcAd::all();

        return view('public-profile', [
            'user' => $user, 'posts' => $posts, 'ads' => $ads, 'followersCount' => $followersCount
        ]);
    }
}
