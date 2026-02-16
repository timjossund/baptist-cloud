<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-4xl text-gray-800 leading-tight">
                            Latest Sermons from those you follow:
                        </h2>
                        <a href="/search-sermons" class="px-2 py-2 text-white bg-gray-200 rounded-md">
                            <svg class="w-6 h-6 text-gray-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                            </svg>
                        </a>
                    </div>
                    @if ($sermons->count() == 0)
                        <p class="text-gray-600">No sermons found</p>
                    @else
                        @foreach ($sermons as $sermon)
                            <x-sermon-item :sermon="$sermon" :ads="$ads" />
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
