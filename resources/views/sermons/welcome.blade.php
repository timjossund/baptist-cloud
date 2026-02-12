<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-4xl text-gray-800 leading-tight ml-6">
                        Latest Sermons from those you follow:
                        @if ($sermons->count() == 0)
                            <p class="text-gray-600">No sermons found</p>
                        @else
                            @foreach ($sermons as $sermon)
                                <x-sermon-item :sermon="$sermon" :ads="$ads" />
                            @endforeach
                        @endif
                    </h2>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
