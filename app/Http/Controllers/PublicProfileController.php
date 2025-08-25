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
            $posts = $user->posts()->with('category', 'likes')->whereNotNull('published_at')->latest('published_at')->simplePaginate(10);
        } else {
            $posts = $user->posts()->with('category', 'likes')->latest()->simplePaginate(10);
        }
        $ads = BcAd::all();

        return view('public-profile', [
            'user' => $user, 'posts' => $posts, 'ads' => $ads, 'followersCount' => $followersCount
        ]);
    }
}
