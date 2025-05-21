<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index() {
        return view('create-listing');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'position' => 'required|max:255',
            'church' => 'required|max:255',
            'city' => 'required|max:255',
            'state' => 'required|max:255',
            'content' => 'required',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'facebook' => 'required|max:255',
            'website' => 'required|max:255',
            'published_at' => ['nullable', 'timestamp'],
        ]);

        $data['city'] = strip_tags($data['city']);
        $data['state'] = strip_tags($data['state']);
        $data['position'] = strip_tags($data['position']);
        $data['content'] = strip_tags($data['content']);
        $data['church'] = strip_tags($data['church']);
        $data['email'] = strip_tags($data['email']);
        $data['phone'] = strip_tags($data['phone']);
        $data['facebook'] = strip_tags($data['facebook']);
        $data['website'] = strip_tags($data['website']);
        $data['content'] = Str::markdown($data['content']);

        Listing::create($data);

        return redirect("/positions")->with('success', 'Listing Created Successfully');
    }

    public function showPositions()
    {
        $positions = Listing::paginate(10)->sortByDesc('created_at');
        return view('positions', ['positions' => $positions]);
    }

    public function showPosition($id)
    {
        $position = Listing::find($id);
        return view('single-position', ['position' => $position]);
    }

    public function edit($id) {

        $listing = Listing::find($id);
        //dd($position);
        return view('edit-listing', ['listing' => $listing]);
    }

    public function update(Request $request, $id) {

        $data = $request->validate([
            'position' => 'required|max:255',
            'church' => 'required|max:255',
            'city' => 'required|max:255',
            'state' => 'required|max:255',
            'content' => 'required',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'facebook' => 'required|max:255',
            'website' => 'required|max:255',
            'published_at' => ['nullable', 'timestamp'],
        ]);

        $data['city'] = strip_tags($data['city']);
        $data['state'] = strip_tags($data['state']);
        $data['position'] = strip_tags($data['position']);
        $data['content'] = strip_tags($data['content']);
        $data['church'] = strip_tags($data['church']);
        $data['email'] = strip_tags($data['email']);
        $data['phone'] = strip_tags($data['phone']);
        $data['facebook'] = strip_tags($data['facebook']);
        $data['website'] = strip_tags($data['website']);
        $data['content'] = Str::markdown($data['content']);

        $position = Listing::find($id);

        $position->update($data);

        return redirect("/positions")->with('success', 'Listing Updated Successfully');
    }
}
