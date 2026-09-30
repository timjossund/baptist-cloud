<?php

namespace App\Http\Controllers;

use App\Models\BcAd;
use App\Models\Sermon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SermonController extends Controller
{
    public function welcome()
    {
        $user = auth()->user();
        $sermons = Sermon::latest()->take(10)->get();
        $ads = BcAd::all();

        if ($user) {
            $userIds = $user->following()->pluck('users.id');
            $sermons = Sermon::whereIn('user_id', $userIds)->latest()->take(10)->get();
        }

        return view('sermons.welcome', compact('sermons', 'ads'));
    }

    public function index()
    {
        $sermons = Sermon::latest()->take(10)->get();
        $ads = BcAd::all();

        return view('sermons.index', compact('sermons', 'ads'));
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
            'audio_url' => ['required', 'file', 'mimes:mp3,audio/mpeg,audio/mpga', 'max:102400'],
            'video_url' => ['nullable'],
            'image_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:6048'],
            'tags' => ['nullable'],
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
        $sermonImage = $data['image_url'] ?? null;
        Sermon::create([
            'title' => $data['title'],
            'slug' => $sermonSlug,
            'description' => $data['description'],
            'series_title' => $data['series_title'] ?? null,
            'audio_url' => $audioUrl,
            'video_url' => $data['video_url'] ?? '',
            'image_url' => $sermonImage,
            'user_id' => auth()->id(),
            'tags' => $data['tags'] ?? null,
        ]);

        return redirect()->route('sermons.index')->with('success', 'Sermon created');
    }

    public function show(Sermon $sermon)
    {
        $ads = BcAd::all();

        return view('sermons.single', compact('sermon', 'ads'));
    }

    public function edit(Sermon $sermon)
    {
        if ($sermon->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        return view('sermons.edit', compact('sermon'));
    }

    public function update(Request $request, Sermon $sermon)
    {
        if ($sermon->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required'],
            'description' => ['required'],
            'series_title' => ['nullable'],
            'audio_url' => ['nullable', 'file', 'mimes:mp3,audio/mpeg,audio/mpga', 'max:102400'],
            'video_url' => ['nullable'],
            'image_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:6048'],
            'tags' => ['nullable'],
        ]);

        $sermonSlug = str()->slug($data['title']);

        if ($request->hasFile('audio_url')) {
            $sermonName = $sermonSlug.'-'.uniqid('', true).'.mp3';
            $oldAudio = $sermon->audio_url;
            Storage::disk('sermons')->putFileAs('sermons/', $request->file('audio_url'), $sermonName);
            $this->deleteStored('sermons', 'sermons/', $oldAudio);
            $data['audio_url'] = 'https://s3.us-central-1.ionoscloud.com/sermons-bc/sermons/'.$sermonName;
        }

        if ($request->hasFile('image_url')) {
            $oldImage = $sermon->getRawOriginal('image_url');
            $sermonImage = 'new-image'.'-'.$sermonSlug.'-'.uniqid('3', true).'.jpg';
            $manager = new ImageManager(new Driver);
            $image = $manager->read($request->file('image_url'));
            $imgNew = $image->cover(400, 400)->toJpeg();
            Storage::disk('postImages')->put('sermon-images/'.$sermonImage, $imgNew);
            $this->deleteStored('postImages', 'sermon-images/', $oldImage);

            $data['image_url'] = 'https://s3.us-central-1.ionoscloud.com/post-images/sermon-images/'.$sermonImage;
        }

        $sermon->fill($data);
        $sermon->save();

        return redirect()->route('sermons.index')->with('success', 'Sermon updated');
    }

    public function destroy(Sermon $sermon)
    {
        if ($sermon->user_id != auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }
        $this->deleteStored('sermons', 'sermons/', $sermon->audio_url);
        $this->deleteStored('postImages', 'sermon-images/', $sermon->getRawOriginal('image_url'));
        $sermon->delete();

        return redirect()->route('sermons.index')->with('success', 'Sermon deleted');
    }

    /**
     * Delete a stored file given the public URL we saved for it.
     */
    private function deleteStored(string $disk, string $directory, ?string $url): void
    {
        $name = $url ? basename((string) parse_url($url, PHP_URL_PATH)) : '';

        if ($name !== '') {
            Storage::disk($disk)->delete($directory.$name);
        }
    }

    public function searchAudio()
    {
        return view('search-sermons');
    }
}
