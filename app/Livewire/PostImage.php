<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostImage extends Component
{
    use WithFileUploads;
    public $image = '';
    public function render()
    {
        $image = $this->image;
        return view('livewire.post-image' , ['image' => $image]);
    }
}
