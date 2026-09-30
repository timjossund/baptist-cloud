<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render user-supplied markdown with raw HTML and unsafe links disabled.
     */
    public static function render(?string $text): string
    {
        return Str::markdown($text ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
