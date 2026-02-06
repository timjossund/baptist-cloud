<?php

namespace App\Http\Controllers;

use App\Models\Sermon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'audio' => 'required|file|mimes:mp3,wav,aac,m4a|max:604800',
            'video' => 'required',
            'image' => 'required',
            'published_at' => 'required',
        ]);

        $audioFile = $request->file('audio');
        $tempPath = $audioFile->store('temp', 'local');
        $localPath = Storage::disk('s3')->put('sermons/temp', $audioFile);

        $fileName = pathinfo($audioFile->hashName(), PATHINFO_FILENAME).'.mp3';
        $outputPath = dirname($localPath).'/'.$fileName;

        // Convert to MP3
        $ffmpeg = \FFMpeg\FFMpeg::create();
        $audio = $ffmpeg->open($localPath);
        $format = new \FFMpeg\Format\Audio\Mp3;
        $audio->save($format, $outputPath);

        // Upload to S3
        $s3Path = 'sermons/'.$fileName;
        Storage::disk('s3')->put($s3Path, file_get_contents($outputPath));

        // Cleanup
        Storage::disk('local')->delete($tempPath);
        @unlink($outputPath);

        // Handle Image Upload or URL
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('sermons/images', 'public')
            : $request->image;

        $sermon = Sermon::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'audio' => $s3Path,
            'video' => $request->video,
            'image' => $imagePath,
            'published_at' => $request->published_at,
        ]);
        $sermon->save();

        return redirect()->route('sermons.index');
    }
}
