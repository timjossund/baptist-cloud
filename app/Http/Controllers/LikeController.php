<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function likeUnlike(Post $post) {
        $post->likes()->toggle(auth()->user());

        return response()->json(['likesCount' => $post->likes()->count() ]);
    }
}
