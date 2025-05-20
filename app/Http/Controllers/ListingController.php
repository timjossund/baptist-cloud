<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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
        $data['state'] = strip_tags($data['city']);
        $data['position'] = strip_tags($data['position']);
        $data['church'] = strip_tags($data['church']);
        $data['email'] = strip_tags($data['email']);
        $data['phone'] = strip_tags($data['phone']);
        $data['facebook'] = strip_tags($data['facebook']);
        $data['website'] = strip_tags($data['website']);

//        $image = $data['image'];
        //unset($data['image']);
        //$data['slug'] = Str::slug($data['title'] . '-' . Str::random(3));



        //$data['user_id'] = auth()->id();

        Listing::create($data);

        return redirect("/positions")->with('success', 'Listing Created Successfully');
    }
}
