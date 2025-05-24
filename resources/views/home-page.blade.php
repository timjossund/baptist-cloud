<x-app-layout>
    <div class="py-4 md:py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="">
                    <nav class="flex space-x-4 p-4 items-center relative" aria-label="Tabs">
                        <x-category-tabs />
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-5 py-6 md:py-0">
        <div class="bg-white py-12 md:pt-10 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
            <div class="mx-auto max-w-2xl lg:max-w-6xl">
                @auth
                    @if (Request::segment(1) == '')
                        <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                            Latest From Those You Follow
                        </h2>
                    @else
                        <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                            Latest in {{ Request::segment(2) }}
                        </h2>
                    @endif
                @else
                    <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
                        Latest Articles
                    </h2>
                @endauth
                <p class="mt-2 text-lg/8 text-gray-600">
                    Wisdom and Insights from a Christian & Baptist perspective.
                </p>
                @auth
                    @if( auth()->user()->followers()->count() === 0 && $posts->count() === 0 )
                        <h3 class="mt-12 mb-8">Hmmm... nothing here. Let's find some authors to follow!</h3>
                        <a href="/search-authors" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">Search Authors</a>
                    @endif
                @endauth
                <div class="mt-10">
                    @foreach ($posts as $post)
                        <x-post-item :post="$post" />
                    @endforeach
                </div>
                <div class="mt-6 pagination-wrapper">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
