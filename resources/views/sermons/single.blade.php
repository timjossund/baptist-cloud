<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <img src="{{ sermon->image }}" alt="{{ sermon->title }}">
                    <h2 class="font-semibold text-4xl text-gray-800 leading-tight ml-6">
                        {{-- {{ sermon->title }} --}} God Is Love
                    </h2>
                    <p class="text-gray-600">
                        {{-- {{ sermon->description }} --}}
                        God is love. 1 John 4:8 - by Pastor Tim Jossund
                    </p>
                    <audio controls>
                        <source src="{{ sermon->audio }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
