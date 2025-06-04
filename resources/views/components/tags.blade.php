@props([
    'tags'
])

@php
    $newTags = [];
    if ($tags) {
        $newTags = explode(',', $tags);
    }
@endphp

@if ($newTags && $newTags != [])
    <p class="my-10">
        <span class="font-bold">
            Article Tags:
        </span> <br><br>
        @foreach ($newTags as $tag)
            <span class="text-gray-500 rounded-2xl bg-gray-200 px-6 py-1">{{ $tag }}</span>
        @endforeach
    </p>
@else
    <p class="my-10">
        <span class="font-bold">
            Article Tags:
        </span> <br><br>
        No tags found.
    </p>
@endif