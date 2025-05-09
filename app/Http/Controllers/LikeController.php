<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function like(Post $post) {

        $hasLiked = auth()->user()->hasLiked($post);

        if ($hasLiked) {
            $post->likes()->delete(['user_id' => auth()->id()]);
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
        }



        return response()->json(['likesCount' => $post->likes()->count() ]);
    }
    public function unlike(Post $post) {
        $post->likes()->delete(['user_id' => auth()->id()]);

        return response()->json(['likesCount' => $post->likes()->count() ]);
    }
}
