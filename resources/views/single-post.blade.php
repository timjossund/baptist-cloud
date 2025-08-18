<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5" x-data="{showForm: true}">
        <div class="bg-white py-12 md:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col ">
            <div class="mx-auto max-w-2xl lg:max-w-6xl">
                <div class="flex w-full flex-wrap">
                    <div class="w-full sm:w-[75%]">
                        <h1 class="md:text-5xl text-3xl">{{ $post->title }}</h1>
                        <p class="text-gray-500 mb-4 mt-2">
                            {{ $post->updated_at->format('n/j/Y') }} - Around
                            {{ $post->readTime() }}
                            @if ($post->readTime() != 1)
                                minutes
                            @else
                                minute
                            @endif read time.
                        </p>
                        <div class="flex flex-col justify-center mb-4">
                            <div class="flex gap-2 pb-4 items-center">
                                <x-user-avatar :user="$post->user" />
                                <p class="!text-gray-500">
                                    <a href="{{ route('public-profile', $post->user) }}" class="!text-gray-500">
                                        {{ $post->user->name }}
                                    </a> <br>
                                    {{ $post->user->bio }}
                                </p>
                                <x-follow-container :user="$post->user">
                                    @if (auth()->user() && auth()->user()->id !== $post->user->id)
                                        <div class="w-full flex justify-center">
                                            <button @click="follow()" x-text="following ? 'Unfollow' : 'Follow'" :class="following ? 'text-white bg-red-500' : 'text-white bg-blue-800'" class="px-6 -mt-5 ml-6 rounded-md py-2"></button>
                                        </div>
                                    @endif
                                </x-follow-container>
                            </div>
                            <p>
                                <span class="text-gray-500 rounded-2xl bg-gray-200 px-6 py-1">
                                    {{ $post->category->title }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col w-full sm:w-[25%] gap-2 pb-4">
                        @if ($post->ad_heading)
                            <x-client_ad :post="$post" />
                        @else
                            <x-bc_ad :ads="$ads" />
                        @endif
                            <x-bc_ad :ads="$ads" />
                    </div>
                </div>

                <x-like-btn :post="$post" />
                <img src="{{ $post->image }}" alt="Featured Image" class="w-full aspect-[3/1] rounded-xl object-cover mb-12">
                <div class="text-lg" id="markdown-body">{!! $post->content !!}</div>
            </div>
            <x-tags :tags="$post->tags" />
            <div class="flex justify-between my-10">
                <a href="{{ url()->previous() }}" wire:navigate class="flex items-center gap-2 text-gray-700 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Go Back
                </a>
                <a href="/report-post/{{$post->id}}" class="text-red-600">Report This Article</a>
            </div>
        </div>
    </div>
</x-app-layout>
