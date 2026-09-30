<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = BcAd::all();

        return view('admin.ad-create', ['ads' => $ads]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable',
            'description' => 'nullable',
            'link' => ['nullable', 'url:http,https', 'max:255'],
            'int' => 'required',
            'published_at' => ['nullable', 'date'],
        ]);

        $data['title'] = strip_tags((string) ($data['title'] ?? ''));
        $data['description'] = strip_tags((string) ($data['description'] ?? ''));
        $data['link'] = strip_tags((string) ($data['link'] ?? ''));
        $data['int'] = (int) $data['int'];

        BcAd::create($data);

        return redirect('/create-ad')->with('success', 'Ad Created');
    }

    public function edit(BcAd $ad)
    {
        return view('admin.ad-edit', ['ad' => $ad]);
    }

    public function update(Request $request, BcAd $ad)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'link' => ['required', 'url:http,https', 'max:255'],
            'int' => 'required',
        ]);

        $data['title'] = strip_tags((string) ($data['title'] ?? ''));
        $data['description'] = strip_tags((string) ($data['description'] ?? ''));
        $data['link'] = strip_tags((string) ($data['link'] ?? ''));
        $data['int'] = (int) $data['int'];

        $ad->update($data);

        return redirect('/create-ad')->with('success', 'Ad Updated');
    }

    public function delete(BcAd $ad)
    {
        $ad->delete();

        return redirect('/create-ad')->with('success', 'Ad Deleted');
    }
}
