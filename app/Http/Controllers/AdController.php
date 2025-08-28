<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        $ads = BcAd::all();

        return view('admin.ad-create', ['ads' => $ads]);
    }

    public function store(Request $request)
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        $data = $request->validate([
            'title' => 'nullable',
            'description' => 'nullable',
            'link' => 'nullable',
            'int' => 'required',
            'published_at' => ['nullable', 'date'],
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['description'] = strip_tags($data['description']);
        $data['link'] = strip_tags($data['link']);
        $data['int'] = (int)$data['int'];

        BcAd::create($data);

        return redirect("/create-ad")->with('success', 'Ad Created');
    }

    public function edit(BcAd $ad)
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        return view('admin.ad-edit', ['ad' => $ad]);
    }

    public function update(Request $request, BcAd $ad)
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        $data = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'link' => 'required',
            'int' => 'required'
        ]);

        $data['title'] = strip_tags($data['title']);
        $data['description'] = strip_tags($data['description']);
        $data['link'] = strip_tags($data['link']);
        $data['int'] = (int)$data['int'];

        $ad->update($data);

        return redirect("/create-ad")->with('success', 'Ad Updated');
    }

    public function delete(BcAd $ad)
    {
//        if (!auth()->user()->is_admin) {
//            abort(403);
//        }
        $ad->delete();
        return redirect("/create-ad")->with('success', 'Ad Deleted');
    }
}
