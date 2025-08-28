<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index()
    {
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
            'published_at' => ['nullable', 'date'],
        ]);

        $data = $this->getArr($data);

        Listing::create($data);

        return redirect("/positions")->with('success', 'Listing Created Successfully');
    }

    /**
     * @param array $data
     * @return array
     */

    public function getArr(array $data): array
    {
        $data['city'] = strip_tags($data['city']);
        $data['state'] = strip_tags($data['state']);
        $data['position'] = strip_tags($data['position']);
        $data['content'] = strip_tags($data['content']);
        $data['church'] = strip_tags($data['church']);
        $data['email'] = strip_tags($data['email']);
        $data['phone'] = strip_tags($data['phone']);
        $data['facebook'] = strip_tags($data['facebook']);
        $data['website'] = strip_tags($data['website']);
        return $data;
    }

    public function showPositions()
    {
        $positions = Listing::latest()->cursorPaginate(10);
        return view('positions', ['positions' => $positions]);
    }

    public function showPosition(Listing $position)
    {
//        $position = Listing::find($id);
        $position['content'] = Str::markdown($position->content);
        return view('single-position', ['position' => $position]);
    }

    public function edit($id)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $listing = Listing::findOrFail($id);
        //dd($position);
        return view('edit-listing', ['listing' => $listing]);
    }

    public function update(Request $request, $id)
    {

        if (!auth()->user()->is_admin) {
            abort(403);
        }

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

        $data = $this->getArr($data);

        $position = Listing::findOrFail($id);

        $position->update($data);

        return redirect("/positions")->with('success', 'Listing Updated Successfully');
    }
}
