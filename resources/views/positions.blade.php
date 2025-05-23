<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Open Ministry Positions:</h2>
            <div class="flex flex-wrap gap-4">
            @foreach($positions as $position)
                <div class="border-gray-50 border-2 rounded-lg shadow-lg sm:rounded-lg flex flex-col gap-2 p-4 w-5/12">
                    <div class="w-full flex items-center gap-4">
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $position->created_at->format('n/j/Y') }}
                        </p>
                        @if(auth()->user()->is_admin)
                        <a href="/position/{{ $position->id }}/edit" class="flex items-center gap-2 text-blue-500 bg-blue-100 rounded-md px-3 py-2 text-sm font-medium hover:bg-blue-200 w-10 h-10" wire:navigate>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </a>
                    @endif
                    </div>
                    <h2>{{ $position->position }}</h2>
                    <p>{{ $position->church }} in {{ $position->city }}, {{ $position->state }}</p>
                    <a href="/position/{{ $position->id }}" wire:navigate><x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg mt-4">
                        Read More
                    </x-primary-button></a>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
