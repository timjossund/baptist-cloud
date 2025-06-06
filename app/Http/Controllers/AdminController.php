<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Check if the authenticated user is an admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        // Fetch all users and posts from the database
        $users = User::simplePaginate(20);
        $posts = Post::all();
        $likes = Like::all();
        return view('admin.admin', ['users' => $users, 'posts' => $posts, 'likes' => $likes]);
    }

    public function makeAdmin($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_admin = true;
        $user->save();

        return redirect()->back()->with('success', 'Promoted to admin');
    }

    public function makeAuthor($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_author = true;
        $user->save();

        return redirect()->back()->with('success', 'promoted to author');
    }
    public function revokeAdmin($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_admin = false;
        $user->save();

        return redirect()->back()->with('success', 'demoted from admin');
    }
    public function revokeAuthor($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_author = false;
        $user->save();

        return redirect()->back()->with('success', 'demoted from author');
    }
    public function deleteUser($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User deleted');
    }
}
