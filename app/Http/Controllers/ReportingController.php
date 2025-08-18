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

    public function report(Request $request, Post $post) {
        $data = $request->validate([
            'description' => 'required',
            'reporter_email' => 'required',
            'reporter_name' => 'required',
        ]);

        $data['description'] = strip_tags($data['description']);
        $data['reporter_email'] = strip_tags($data['reporter_email']);
        $data['reporter_name'] = strip_tags($data['reporter_name']);
        $data['post_id'] = $post->id;
        $data['post_title'] = $post->title;
        $data['username'] = $post->user->name;
        $data['post_slug'] = $post->slug;


//        $data['post_id'] = strip_tags($data['post_id']);
//        $data['post_title'] = strip_tags($data['post_title']);
//        $data['username'] = strip_tags($data['username']);
//        $data['post_slug'] = strip_tags($data['post_slug']);

        Reporting::create($data);

        return redirect('/')->with('success', 'Article Reported');
    }

    public function delete(Reporting $report)
    {
        abort_unless(auth()->user()->is_admin, 403);
        $report->delete();
        return redirect('/admin/reported-posts')->with('success', 'Report Deleted');
    }
}
