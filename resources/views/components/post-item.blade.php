<article class="relative isolate flex flex-col gap-8 lg:flex-row border-b py-12 w-full">
    <div class="relative aspect-video sm:aspect-[2/1] lg:aspect-square lg:w-64 lg:shrink-0">
        <img src="{{ $post->image }}" alt="" class="absolute inset-0 size-full rounded-2xl bg-gray-50 object-cover">
        <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10"></div>
    </div>
    <div>
        <div class="flex items-center gap-x-4 text-xs">
            <time datetime="2020-03-16" class="text-gray-500 text-sm">Published on {{ $post->created_at->format('n/j/Y') }}</time>
            <a href="#" class="relative z-10 rounded-full bg-gray-50 px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100"></a>
        </div>
        <div class="group relative w-full ">
            <h3 class="mt-1 text-2xl font-semibold text-gray-900 group-hover:text-gray-600">
                <a href="{{ route('single-post', [
                    "username" => $post->user->username,
                    "post" => $post->slug
                    ]) }}">
                    <span class="absolute inset-0"></span>
                    {{ $post->title }}
                    <p class="text-gray-500 mb-2 text-sm">Read time: {{ $post->readTime() }} minutes.</p>
                </a>
            </h3>
            <p class="mt-2 text-md text-gray-600">{{ Str::words($post->content, 16) }}</p>
        </div>
        <div class=" flex border-t border-gray-900/5 pt-2 mb-6">
            <div class="relative flex items-center gap-x-4">
{{--                <img src="{{ $post->user->imageUrl() }}" alt="" class="size-10 rounded-full bg-gray-50">--}}
                <x-user-avatar :user="$post->user" />
                <div class="text-sm/6">
                    <p class="font-semibold text-gray-900">
                        <a href="{{ route('public-profile', $post->user) }}" class="hover:underline">{{ $post->user->name }}</a> - {{ $post->user->bio }}
                    </p>
                </div>
            </div>
        </div>
        @auth
            <a href="{{ route('single-post', [
            "username" => $post->user->username,
            "post" => $post->slug
            ]) }}" class="bg-gray-800 text-white py-2 px-8 rounded-lg">
                Read More
            </a>
        @else
            <a href="{{ route('login') }}" class="bg-gray-900 text-white py-2 px-6 rounded-lg">
                Login to read more
            </a>
        @endauth

    </div>
</article>
