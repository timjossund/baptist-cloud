<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sermon;

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
            'audio' => 'required',
            'video' => 'required',
            'image' => 'required',
            'published_at' => 'required',
        ]);

        $sermon = Sermon::create($request->all());
        $sermon->save();
        return redirect()->route('sermons.index');
    }
}
