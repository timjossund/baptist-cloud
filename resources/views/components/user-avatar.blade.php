@props(['user'])

@if( $user->avatarUrl() )
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-full">
@else
    <img src="/public/default-avatar.jpg" alt="default-avatar" class="w-12 h-12 rounded-full">
@endif
