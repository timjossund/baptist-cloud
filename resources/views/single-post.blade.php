<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="mx-auto max-w-2xl lg:max-w-6xl">
                <h1 class="text-5xl">{{ $post->title }}</h1>
                <p class="text-gray-500 mb-4">Published {{ $post->created_at->format('M d Y') }} -{{ $post->readTime() }} minute read.</p>
                <div class="flex flex-col justify-center mb-4">
                    <div class="flex gap-2 pb-4 items-center">
                        <x-user-avatar :user="$post->user" />
                        <p><a href="{{ route('public-profile', $post->user) }}">{{ $post->user->name }}</a> <br> {{ $post->user->bio }}</p>
                        <x-follow-container :user="$post->user" >
                            @if(auth()->user() && auth()->user()->id !== $post->user->id )
                                <div class="w-full flex justify-center">
                                    <button @click="follow()" x-text="following ? 'unfollow' : 'follow'" :class="following ? 'text-red-500 border border-red-500' : 'text-blue-800 border border-blue-800'" class="px-6 -mt-5 ml-6"></button>
                                </div>
                            @endif
                        </x-follow-container>
                    </div>
                    <p> <span class="text-gray-500 rounded-2xl bg-gray-200 px-6 py-1">{{ $post->category->title }}</span></p>
                </div>
                <x-like-btn :post="$post" />
                <img src="{{ $post->image }}" alt="Featured Image" class="w-full h-96 rounded-xl object-cover mb-12">
                <p class="text-lg">{{ $post->content }}</p>
            </div>
            <p class="my-10">
                <span class="text-gray-500 rounded-2xl bg-gray-200 px-6 py-1">{{ $post->category->title }}</span>
            </p>
        </div>
    </div>
</x-app-layout>
