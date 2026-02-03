<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach ($sermons as $sermon)
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold">{{ $sermon->title }}</h2>
                            <p class="text-gray-600">{{ $sermon->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
