<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 flex gap-6">
                    <div class="flex w-1/4 h-full overflow-hidden">
                        <x-sermon-default :sermon="$sermon" />
                    </div>
                    <div class="flex flex-col px-4 w-2/4">
                        <p class="text-gray-600 text-sm">
                            {{ $sermon->created_at->format('n/j/Y') }}
                        </p>
                        <h2 class="font-semibold text-4xl text-gray-800 leading-tight">
                            {{ $sermon->title }}
                        </h2>
                        <p class="text-gray-600">
                            <span class="font-semibold text-lg">Main Scripture: {{ $sermon->description }}</span><br>
                            Posted by: {{ $sermon->user->name }}
                        </p>
                        <audio controls class="w-full mt-4">
                            <source src="{{ $sermon->audio_url }}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                    <div class="flex flex-col w-1/4 gap-y-2">
                        @if ($sermon->ad_heading)
                            <x-client_ad :sermon="$sermon" />
                        @else
                            <x-bc_ad :ads="$ads" />
                        @endif
                        <x-bc_ad :ads="$ads" />
                    </div>
                </div>
                <div class="p-6 bg-white border-b border-gray-200 flex justify-between">
                    <a href="{{ url()->previous() }}" wire:navigate
                        class="flex items-center gap-2 text-gray-700 hover:text-gray-700 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                        Go Back
                    </a>
                    <a href="/report-post/{{ $sermon->id }}" class="text-red-600">Report This Sermon</a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
