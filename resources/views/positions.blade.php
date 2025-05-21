<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Open Ministry Positions:</h2>
            <div class="flex flex-wrap gap-4">
            @foreach($positions as $position)
                <div class="border-gray-50 border-2 rounded-lg shadow-lg sm:rounded-lg flex flex-col gap-2 p-4 w-5/12">
                    <p>{{ $position->created_at->format('n/j/Y') }}
                    @if(auth()->user()->is_admin)
                        <a href="/position/{{ $position->id }}/edit" class="flex items-center gap-2 text-gray-700 hover:text-gray-700 mt-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                            Edit
                        </a>
                    @endif
                        </p>
                    <h2>{{ $position->position }}</h2>
                    <p>{{ $position->church }} in {{ $position->city }}, {{ $position->state }}</p>
                    <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg mt-4">
                        <a href="/position/{{ $position->id }}">Read More</a>
                    </x-primary-button>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
