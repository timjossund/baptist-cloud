@props(['sermon'])

@if ($sermon->image_url)
    <img src="{{ $sermon->image_url }}" alt="{{ $sermon->title }}" class="w-full h-full object-cover rounded-lg">
@else
    <img src="/public/avatars/bible-sermon-placeholder.jpg" alt="default-avatar"
        class="w-full h-full object-cover rounded-lg">
@endif
