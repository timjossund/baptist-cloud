<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Url;

class Search extends Component
{
    public $search = '';
    public function render()
    {
        if (empty($this->search)) {
            return view('livewire.search');
        }
        return view('livewire.search', [
            'users' => User::query()->where('name', 'LIKE', "%{$this->search}%")->get(),
            'posts' => Post::query()->where('title', 'LIKE', "%{$this->search}%")->orWhere('tags', 'LIKE', "%{$this->search}%")->get()->whereNotNull('published_at'),
        ]);
    }
}
