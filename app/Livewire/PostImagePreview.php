<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PostImagePreview extends Component
{
    #[Locked]
    public Post $post;

    public function render()
    {
        $this->post->refresh();

        return view('livewire.post-image-preview', [
            'processing' => ! $this->post->getRawOriginal('image'),
        ]);
    }
}
