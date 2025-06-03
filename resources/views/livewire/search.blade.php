<div class="flex flex-wrap">
    <input type="text" wire:model.live="search" class="w-full" placeholder="Start typing a search..." autofocus>
    <div class="w-1/2 mt-4 pr-4">
        <h3 class="mb-2">Results in users:</h3>
        <ul class="flex flex-col gap-4">
        @if ($search)
        @foreach ($users as $user)
            <a href="{{ route('public-profile', $user->username) }}" wire:navigate>
                <li wire:key="{{ $user->id }}" class="w-full shadow-md py-2 px-4 flex gap-4 items-center rounded">
                    <img src="{{ $user->avatar }}" alt="" class="aspect-[1/1] h-10 w-10 rounded-full">
                    <h4>{{ $user->name }}</h4>
                </li>
            </a>
        @endforeach
        @endif
        </ul>
    </div>
    <div class="w-1/2 mt-4">
        <h3 class="mb-2">Results in articles:</h3>
        <ul class="flex flex-col gap-4">
        @if ($search)
        @foreach ($posts as $post)
            <a href="{{ route('single-post', ['username' => $post->user->username, 'post' => $post->slug]) }}">
                <li wire:key="{{ $post->id }}" class="w-full shadow-md py-2 px-4 flex gap-4 items-center rounded">
                    <img src="{{ $post->image }}" alt="" class="aspect-[1/1] h-10 w-10 rounded">
                    <h4>{{ $post->title }}</h4>
                </li>
            </a>
        @endforeach
        @endif
        </ul>
    </div>
</div>
