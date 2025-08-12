<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reporting;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function index(Post $post) {
        return view('post-reporting', ['post' => $post]);
    }

    public function report(Request $request) {
        $data = $request->validate([
            'description' => 'required',
            'reporter_email' => 'required',
            'reporter_name' => 'required',
            'post_id' => 'required',
            'post_title' => 'required',
            'username' => 'required',
            'post_slug' => 'required'
        ]);

        $data['description'] = strip_tags($data['description']);
        $data['reporter_email'] = strip_tags($data['reporter_email']);
        $data['reporter_name'] = strip_tags($data['reporter_name']);
        $data['post_id'] = strip_tags($data['post_id']);
        $data['post_title'] = strip_tags($data['post_title']);
        $data['username'] = strip_tags($data['username']);
        $data['post_slug'] = strip_tags($data['post_slug']);

        Reporting::create($data);

        return redirect('/')->with('success', 'Article Reported');
    }
}
