<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Open Ministry Positions:</h2>
            <div class="flex flex-wrap gap-4">
            @foreach($positions as $position)
                <div class="bg-gray-50 rounded-lg shadow-lg flex flex-col md:flex-row items-start md:items-end gap-2 pt-4 px-4 w-full relative pb-14 md:pb-4 min-h-[200px] md:min-h-[100px]">
                    <div class="flex flex-col gap-2 md:mr-4">
                        <div class="flex gap-4">
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
                        <div class="flex gap-0 md:gap-4 flex-col md:flex-row">
                            <h2 class="text-2xl md:text-3xl font-bold">{{ $position->position }}</h2>
                            <p class="">{{ $position->church }}, {{ $position->city }}, {{ $position->state }}</p>
                        </div>
                    </div>
                    <a href="/position/{{ $position->id }}" wire:navigate class="absolute bottom-0 left-0 md:left-auto md:right-0 md:h-full flex items-center justify-center w-full md:w-auto rounded-bl-lg md:rounded-bl-none md:rounded-tr-lg rounded-br-lg bg-gray-800 cursor-pointer">
                        <div class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                            Read More
                        </div>
                    </a>
                </div>
            @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
