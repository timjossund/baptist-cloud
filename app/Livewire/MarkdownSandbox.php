<?php

namespace App\Livewire;

use App\Support\Markdown;
use Livewire\Component;

class MarkdownSandbox extends Component
{
    public $markdown = '';

    public function render()
    {
        $markdownText = $this->markdown;
        $content = Markdown::render($markdownText);

        return view('livewire.markdown-sandbox', ['content' => $content]);
    }
}
