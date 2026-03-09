<?php

namespace App\Livewire;

use App\Models\Sermon;
use App\Models\User;
use Livewire\Component;

class SearchAudio extends Component
{
    public $search = '';

    public function render()
    {
        if (empty($this->search)) {
            return view('livewire.search-audio');
        }

        return view('livewire.search-audio', [
            'users' => User::query()->where('name', 'LIKE', "%{$this->search}%")->get(),
            'sermons' => Sermon::query()->where('title', 'LIKE', "%{$this->search}%")->orWhere('tags', 'LIKE', "%{$this->search}%")->get()->whereNotNull('published_at'),
        ]);
    }
}
