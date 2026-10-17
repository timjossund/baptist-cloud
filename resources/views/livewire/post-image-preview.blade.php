<div @if ($processing) wire:poll.2s @endif>
    @if ($processing)
        <div class="flex items-center justify-center rounded-lg aspect-[3/1] w-full bg-gray-100 animate-pulse">
            <span class="text-sm text-gray-500">Processing image…</span>
        </div>
        <p class="mt-2 text-sm text-gray-500">Your featured image is still processing. It will appear here in a moment.</p>
    @else
        <img src="{{ $post->image }}" alt="Featured Image" class="rounded-lg aspect-[3/1]">
    @endif
</div>
