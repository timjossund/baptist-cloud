<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Reporting;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::simplePaginate(10);
        $userCount = User::count();
        $postCount = Post::count();
        $likesCount = Like::count();

        return view('admin.admin', ['users' => $users, 'postCount' => $postCount, 'userCount' => $userCount, 'likesCount' => $likesCount]);
    }

    public function makeAdmin(User $user)
    {
        $user->is_admin = true;
        $user->save();

        return redirect()->back()->with('success', 'Promoted To Admin');
    }

    public function revokeAdmin(User $user)
    {
        if (auth()->user()->username !== config('app.super_admin_username') || auth()->user()->id === $user->id) {
            abort(403);
        }
        $user->is_admin = false;
        $user->save();

        return redirect()->back()->with('success', 'demoted from admin');
    }

    public function deleteUser(User $user)
    {
        if (! $user->is_admin) {
            $user->delete();

            return redirect()->back()->with('success', 'User deleted');
        } else {
            return redirect()->back()->with('error', 'Cannot delete admin');
        }
    }

    public function reported()
    {
        $reports = Reporting::paginate(10);

        return view('admin.reported-posts', ['reports' => $reports]);
    }
}
