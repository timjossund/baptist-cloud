<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">{{ $position->position }}</h2>
            <div class="flex flex-col">
                <p>Listing created on {{ $position->created_at->format('n/j/Y') }}</p>
                <p class="text-2xl mt-4 mb-4">{{ $position->church }} <br> {{ $position->city }}, {{ $position->state }}</p>
                <div class="prose my-4">
                    <div class="pb-2">
                        <h3>Position Details:</h3>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 rounded-lg">
                        {!! $position->content !!}
                    </div>
                </div>
                <h3>Contact Information:</h3>
                <p class="text-lg mt-4">Email: <a href="mailto:{{ $position->email }}">{{ $position->email }}</a></p>
                <p class="text-lg mt-4">Phone: <a href="tel:{{ $position->phone }}">{{ $position->phone }}</a></p>
                <p class="text-lg mt-4">Facebook: <a href="{{ $position->facebook }}" target="_blank">{{ $position->facebook }}</a></p>
                <p class="text-lg mt-4">Website: <a href="{{ $position->website }}" target="_blank">{{ $position->website }}</a></p>
                <a href="{{ url()->previous() }}" wire:navigate class="flex items-center gap-2 text-gray-700 hover:text-gray-700 mt-10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="size-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Go Back
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
