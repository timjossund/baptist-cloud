<?php

namespace App\Http\Controllers;

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
        // Fetch all users from the database
        $users = User::all();
        return view('admin.admin', ['users' => $users]);
    }

    public function makeAdmin($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_admin = true;
        $user->save();

        return redirect()->back()->with('success', 'User promoted to admin successfully.');
    }

    public function makeAuthor($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_author = true;
        $user->save();

        return redirect()->back()->with('success', 'User promoted to author successfully.');
    }
    public function revokeAdmin($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_admin = false;
        $user->save();

        return redirect()->back()->with('success', 'User demoted from admin successfully.');
    }
    public function revokeAuthor($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->is_author = false;
        $user->save();

        return redirect()->back()->with('success', 'User demoted from author successfully.');
    }
    public function deleteUser($id)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
