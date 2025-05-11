<a href="/"
    class="{{ Request::segment(2) == '' ? 'rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active' : 'rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700' }} rounded-lg flex justify-center items-center"
    aria-current="page">
    @auth
        Following
    @else
        All
    @endauth

</a>
<div class="flex items-center border-l-2 border-gray-400 pl-4">
    Browse More By Topic:
</div>
@foreach ($categories as $category)
    <a href="{{ route('byCategory', $category) }}"
        class="inline-block px-4 py-3 {{ Route::currentRouteNamed('byCategory') && request('category')->id == $category->id ? 'rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 active' : 'rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700' }} rounded-lg"
        aria-current="page">
        {{ $category->title }}
    </a>
@endforeach
