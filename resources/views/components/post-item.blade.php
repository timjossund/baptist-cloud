<article class="relative isolate flex flex-col gap-8 lg:flex-row border-b py-12 w-full">
    <div class="relative aspect-video sm:aspect-[2/1] lg:aspect-square lg:w-64 lg:shrink-0">
        <img src="{{ $post->image }}" alt="post image" class="absolute inset-0 size-full rounded-2xl bg-gray-50 object-cover">
    </div>
    <div>
        <div class="flex items-center gap-x-4 text-xs">
            <time datetime="2020-03-16" class="text-gray-500 text-sm">
                @if($post->published_at == null )
                    <span class="text-lg font-black text-black bg-blue-300 py-1 px-4">Draft</span> - created on {{ $post->created_at->format('n/j/Y') }}
                @else
                    {{ $post->updated_at->format('n/j/Y') }}
                @endif
            </time>
        </div>
        <div class="flex flex-col">
            <h3 class="mt-1 text-2xl flex gap-2 items-center font-semibold text-gray-900 group-hover:text-gray-600">
                <a href="{{ route('single-post', ['username' => $post->user->username, 'post' => $post->slug]) }}" class="flex">
                    {{ Str::limit($post->title, 55) }}
                </a>
            </h3>
            <p class="text-gray-500 text-sm">Read time: {{ $post->readTime() }}
                @if ($post->readTime() != 1)
                    minutes.
                @else
                    minute.
                @endif
            </p>
            <div class="flex gap-2 mt-2">
                <p class="flex gap-3 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="size-6 text-gray-500">
                        <path
                            d="M7.493 18.5c-.425 0-.82-.236-.975-.632A7.48 7.48 0 0 1 6 15.125c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75A.75.75 0 0 1 15 2a2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23h-.777ZM2.331 10.727a11.969 11.969 0 0 0-.831 4.398 12 12 0 0 0 .52 3.507C2.28 19.482 3.105 20 3.994 20H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 0 1-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227Z" />
                    </svg>
                    {{ $post->likes()->count() }}
                </p>
                @if (auth()->user() && auth()->user()->id == $post->user_id)
                    <a href="/post/{{ $post->slug }}/edit" class="text-blue-500 hover:underline text-sm bg-blue-100 p-2 rounded-md h-9">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </a>
                @endif
                @if (auth()->user() && (auth()->user()->is_admin != null || auth()->user()->id == $post->user_id))
                    <form action="/post/{{ $post->id }}/delete" method="post"
                        class="text-blue-500 hover:underline text-sm">
                        @csrf
                        @method('delete')
                        <button type="submit" class="flex items-center bg-red-100 p-2 rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class=" flex border-t border-gray-900/5 pt-2 mb-4">
            <div class="relative flex items-center gap-x-4">
                <x-user-avatar :user="$post->user" />
                <div class="text-sm/6">
                    <p class="font-semibold !text-gray-500">
                        <a href="{{ route('public-profile', $post->user) }}" class="hover:underline !text-gray-500">
                            {{ $post->user->name }}
                        </a>
                        - {{ $post->user->bio }}
                    </p>
                </div>
            </div>
        </div>
        @auth
            <a href="{{ route('single-post', [ 'username' => $post->user->username, 'post' => $post->slug ]) }}" class="bg-gray-800 text-white py-2 px-8 rounded-md">
                Read More
            </a>
        @else
            <a href="{{ route('login') }}" class="bg-gray-900 text-white py-2 px-6 rounded-md">
                Login to read more
            </a>
        @endauth
    </div>
</article>
