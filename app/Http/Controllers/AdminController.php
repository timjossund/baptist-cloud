<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Reporting;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Check if the authenticated user is an admin
//        if (!auth()->user() || !auth()->user()->is_admin) {
//            abort(403);
//        }
        // Fetch all users and posts from the database
        $users = User::simplePaginate(10);
        $userCount = User::count();
        $postCount = Post::count();
        $likesCount = Like::count();
        return view('admin.admin', ['users' => $users, 'postCount' => $postCount, 'userCount' => $userCount, 'likesCount' => $likesCount]);
    }

    public function makeAdmin(User $user)
    {
//        if (!auth()->user() || !auth()->user()->is_admin) {
//            abort(403);
//        }
        $user->is_admin = 1;
        $user->save();

        return redirect()->back()->with('success', 'Promoted To Admin');
    }

    public function makeAuthor(User $user)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user->is_author = true;
        $user->save();

        return redirect()->back()->with('success', 'promoted to author');
    }
    public function revokeAdmin(User $user)
    {
        if (auth()->user()->username != 'timjossund' || auth()->user()->id === $user->id) {
            abort(403);
        }
        $user->is_admin = false;
        $user->save();

        return redirect()->back()->with('success', 'demoted from admin');
    }
    public function revokeAuthor(User $user)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user->is_author = false;
        $user->save();

        return redirect()->back()->with('success', 'demoted from author');
    }
    public function deleteUser(User $user)
    {
        if (!$user->is_admin) {
            $user->delete();
            return redirect()->back()->with('success', 'User deleted');
        } else {
            return redirect()->back()->with('error', 'Cannot delete admin');
        }
    }

    public function reported()
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        $reports = Reporting::paginate(10);
        return view('admin.reported-posts', ['reports' => $reports]);
    }
}
