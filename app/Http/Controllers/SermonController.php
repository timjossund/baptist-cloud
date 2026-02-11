<?php

namespace App\Http\Controllers;

use App\Models\Sermon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SermonController extends Controller
{
    public function welcome(Sermon $sermon)
    {
        $sermons = $sermon->all();

        return view('sermons.welcome', compact('sermons'));
    }

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
            'title' => ['required'],
            'description' => ['required'],
            'series_title' => ['nullable'],
            'audio_url' => ['required', 'file', 'mimes:mp3,audio/mpeg,audio/mpga'],
            'video_url' => ['nullable'],
            'image_url' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:6048'],
        ]);

        $sermonSlug = str()->slug($data['title']);

        $sermonName = $sermonSlug.'-'.uniqid('', true).'.mp3';
        Storage::disk('sermons')->putFileAs('sermons/', $request->file('audio_url'), $sermonName);

        $sermonImage = null;
        if ($request->hasFile('image_url')) {
            $sermonImage = 'image'.'-'.$sermonSlug.'-'.uniqid('3', true).'.jpg';
            $manager = new ImageManager(new Driver);
            $image = $manager->read($data['image_url']);
            $imgNew = $image->cover(400, 400)->toJpeg();
            Storage::disk('postImages')->put('sermon-images/'.$sermonImage, $imgNew);

            $data['image_url'] = 'https://s3.us-central-1.ionoscloud.com/post-images/sermon-images/'.$sermonImage;
        }

        $audioUrl = 'https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/'.$sermonName;

        Sermon::create([
            'title' => $data['title'],
            'slug' => $sermonSlug,
            'description' => $data['description'],
            'series_title' => $data['series_title'] ?? null,
            'audio_url' => $audioUrl,
            'video_url' => $data['video_url'] ?? '',
            'image_url' => $sermonImage ?? '',
        ]);

        return redirect()->route('sermons.index')->with('success', 'Sermon created');
    }
}
