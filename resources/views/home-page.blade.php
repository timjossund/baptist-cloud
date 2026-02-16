<x-app-layout>
    <div class="py-4 md:py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white shadow-sm rounded-lg flex justify-between items-center pr-4">
                <nav class="flex space-x-4 p-4 items-center relative" aria-label="Tabs">
                    <x-category-tabs />
                </nav>
                <a href="/search" class="px-2 py-2 text-white bg-gray-200 rounded-md h-10 w-10">
                    <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                    </svg>
                </a>
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
                    Wisdom, Insights, & Study Material from a Christian & Baptist perspective.
                </p>
                @auth
                    @if( auth()->user()->followers()->count() === 0 || $posts->count() === 0 )
                        <h3 class="mt-12 mb-8">Hmmm... nothing here. Let's find something to read!</h3>
                        <a href="/search" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">Search Now</a>
                    @endif
                @endauth
                <div class="mt-10">
                    @foreach ($posts as $post)
                        <x-post-item :post="$post" :ads="$ads" />
                    @endforeach
                </div>
                <div class="mt-6 pagination-wrapper">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
