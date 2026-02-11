@props(['sermon'])

@if ($sermon->image_url)
    <img src="{{ $sermon->image_url }}" alt="{{ $sermon->title }}" class="w-120 h-120 object-cover">
@else
    <img src="/public/avatars/bible-sermon-placeholder.jpg" alt="default-avatar" class="w-120 h-120 object-cover">
@endif
