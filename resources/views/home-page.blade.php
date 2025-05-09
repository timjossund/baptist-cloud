<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="">
                    <nav class="flex space-x-4 p-4" aria-label="Tabs">
                         <x-category-tabs />
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
            <div class="mx-auto max-w-2xl lg:max-w-6xl">
                @auth
                    @if(Request::segment(2) == '')
                        <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Latest From Those You Follow</h2>
                    @else
                        <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Latest Articles</h2>
                    @endif
                @else
                <h2 class="text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Latest Articles</h2>
                @endauth
                <p class="mt-2 text-lg/8 text-gray-600">Articles and insights from a Baptist perspective.</p>
                <div class="mt-10">
                    @foreach($posts as $post)
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
