<article class="relative isolate flex flex-col gap-8 lg:flex-row border-b py-12 w-full">
    <div class="relative aspect-video sm:aspect-[2/1] lg:aspect-square lg:w-64 lg:shrink-0">
        <img src="{{ $post->image }}" alt=""
            class="absolute inset-0 size-full rounded-2xl bg-gray-50 object-cover">
        <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
    </div>
    <div>
        <div class="flex items-center gap-x-4 text-xs">
            <time datetime="2020-03-16" class="text-gray-500 text-sm">Published on
                {{ $post->created_at->format('n/j/Y') }}</time>
            <a href="#"
                class="relative z-10 rounded-full bg-gray-50 px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100"></a>
        </div>
        <div class="flex flex-col">
            <h3 class="mt-1 text-2xl font-semibold text-gray-900 group-hover:text-gray-600">
                <a href="{{ route('single-post', [
                    'username' => $post->user->username,
                    'post' => $post->slug,
                ]) }}"
                    class="flex items-center gap-x-2">
                    {{ Str::limit($post->title, 60) }} <span class="text-gray-500 text-sm flex gap-2 pr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                            <path
                                d="M7.493 18.5c-.425 0-.82-.236-.975-.632A7.48 7.48 0 0 1 6 15.125c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75A.75.75 0 0 1 15 2a2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23h-.777ZM2.331 10.727a11.969 11.969 0 0 0-.831 4.398 12 12 0 0 0 .52 3.507C2.28 19.482 3.105 20 3.994 20H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 0 1-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227Z" />
                        </svg>
                        {{ $post->likes()->count() }}
                    </span>
                </a>
            </h3>
            <p class="text-gray-500 mb-2 text-sm">(Read time: {{ $post->readTime() }} minutes)</p>
            <p class="mt-2 text-md text-gray-600">{{ Str::words($post->content, 30) }}</p>
        </div>
        <div class=" flex border-t border-gray-900/5 pt-2 mb-6">
            <div class="relative flex items-center gap-x-4">
                <x-user-avatar :user="$post->user" />
                <div class="text-sm/6">
                    <p class="font-semibold text-gray-900">
                        <a href="{{ route('public-profile', $post->user) }}"
                            class="hover:underline">{{ $post->user->name }}</a> - {{ $post->user->bio }}
                    </p>
                </div>
            </div>
        </div>
        @auth
            <a href="{{ route('single-post', [
                'username' => $post->user->username,
                'post' => $post->slug,
            ]) }}"
                class="bg-gray-800 text-white py-2 px-8 rounded-lg">
                Read More
            </a>
        @else
            <a href="{{ route('login') }}" class="bg-gray-900 text-white py-2 px-6 rounded-lg">
                Login to read more
            </a>
        @endauth
        <div>
            <p class="text-gray-500 mb-2 text-sm"></p>
        </div>
    </div>
</article>
