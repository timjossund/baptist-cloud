<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;

class MarkdownSandbox extends Component
{
    public $markdown = '';

    public function render()
    {
        $markdownText = $this->markdown;
        $content = Str::markdown($markdownText);
        return view('livewire.markdown-sandbox' , ['content' => $content]);
    }
}
