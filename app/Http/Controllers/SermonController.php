<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sermon;
use Illuminate\Support\Facades\Storage;

class SermonController extends Controller
{
    public function index()
    {
        $sermons = Sermon::all();
        return view('sermons.index', compact('sermons'));
    }

    public function create()
    {
        return view('sermons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'series_title' => ['nullable', 'string'],
            'audio_url' => ['required', 'file', 'mimes:mp3,audio/mpeg,audio/mpga'],
            'video_url' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg'],
        ]);

        $sermonSlug = str()->slug($data['title']);

        $sermonName = $sermonSlug . '-' . uniqid('', true) . '.mp3';
        Storage::disk('sermons')->putFileAs('sermons/', $request->file('audio_url'), $sermonName);

        $imageName = null;
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = $sermonSlug . '-' . uniqid('', true) . '.' . $image->getClientOriginalExtension();
            Storage::disk('sermons')->putFileAs('sermons/', $image, $imageName);
        }

        $audioUrl = 'https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/' . $sermonName;

        Sermon::create([
            'title' => $data['title'],
            'slug' => $sermonSlug,
            'description' => $data['description'],
            'series_title' => $data['series_title'] ?? null,
            'audio_url' => $audioUrl,
            'video_url' => $data['video_url'] ?? '',
            'image_url' => $imageName ?? '',
        ]);
        return redirect()->route('sermons.index')->with('success', 'Sermon created successfully');
    }
}
