<a href="/"
    class="{{ Request::segment(2) == '' ? 'rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active' : 'rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700' }} rounded-lg flex justify-center items-center max-h-9 absolute md:static top-2 left-2 z-10"
    aria-current="page">
    @auth
        Following
    @else
        All
    @endauth
</a>
<div class="items-center border-l-2 border-gray-400 pl-4 hidden md:flex">
    Browse More By Topic:
</div>
<div class="items-center hidden md:flex">
@foreach ($categories as $category)
    <a href="{{ route('byCategory', $category) }}"
        class="inline-block px-4 py-3 {{ Route::currentRouteNamed('byCategory') && request('category')->id == $category->id ? 'rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active' : 'rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700' }} rounded-lg"
        aria-current="page">
        {{ $category->title }}
    </a>
@endforeach
</div>

<div x-data="{ cat_open: false }" class="relative w-full flex justify-end items-center flex-wrap">
    <h6 class="mr-16">Topics</h6>
    <div class="flex md:hidden absolute right-0 -top-2">
        <button @click="cat_open = ! cat_open"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': cat_open, 'inline-flex': !cat_open }" class="inline-flex"
                      stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': !cat_open, 'inline-flex': cat_open }" class="hidden" stroke-linecap="round"
                      stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

        <div :class="{ 'block': cat_open, 'hidden': !cat_open }" class="bg-white top-10 w-full">

        <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t mt-4 border-gray-200 flex justify-start flex-wrap">


                @foreach ($categories as $category)
                    <a href="{{ route('byCategory', $category) }}"
                       class="inline-block px-4 py-3 {{ Route::currentRouteNamed('byCategory') && request('category')->id == $category->id ? 'rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active' : 'rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700' }} rounded-lg"
                       aria-current="page">
                        {{ $category->title }}
                    </a>
                @endforeach

        </div>
    </div>
</div>
