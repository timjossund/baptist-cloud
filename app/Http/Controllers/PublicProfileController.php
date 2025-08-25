<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(User $user)
    {
        $posts = $user->posts()->latest()->simplePaginate(10);
        $ads = BcAd::all();

        return view('public-profile', [
            'user' => $user, 'posts' => $posts, 'ads' => $ads
        ]);
    }
}
