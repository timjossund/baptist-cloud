@props([
    'tags'
])

@php
    $newTags = [];
    if ($tags) {
        $newTags = array_values(array_filter(array_map('trim', explode(',', $tags)), fn($t) => $t !== ''));
    }
@endphp

@if ($newTags && $newTags != [])
    <p class="my-10 font-bold">
        Article Tags:
     </p>
    <div class="flex flex-wrap gap-2">
        @foreach ($newTags as $tag)
            <div class="text-gray-600 rounded-2xl bg-gray-200 px-4 py-1">
                {{ $tag }}
            </div>
        @endforeach
    </div>

@else
    <p class="my-10">
        <span class="font-bold">
            Article Tags:
        </span> <br><br>
        No tags found.
    </p>
@endif
