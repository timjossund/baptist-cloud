<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BcAd;

class AdController extends Controller
{
    public function index()
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403);
        }
        $ads = BcAd::all();
        return view('admin.ad-create', ['ads' => $ads]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable',
            'description' => 'nullable',
            'link' => 'nullable',
            'published_at' => ['nullable', 'timestamp'],
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['description'] = strip_tags($data['description']);
        $data['link'] = strip_tags($data['link']);

        BcAd::create($data);

        return redirect("/create-ad")->with('success', 'Ad Created');
    }
}
