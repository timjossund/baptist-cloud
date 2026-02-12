<article class="relative isolate flex flex-col gap-4 lg:flex-row border-b py-12 w-full">
    <div class="relative aspect-video sm:aspect-[2/1] lg:aspect-square lg:w-64 lg:shrink-0">
        <x-sermon-default :sermon="$sermon" />
    </div>
    <div class="sm:w-[55%]">
        <div class="flex items-center gap-x-4 text-xs">
            <time datetime="2020-03-16" class="text-gray-500 text-sm">
                {{ $sermon->created_at->format('n/j/Y') }}
            </time>
        </div>
        <div class="flex flex-col">
            <h3 class="mt-1 text-2xl flex gap-2 items-center font-semibold text-gray-900 group-hover:text-gray-600">
                {{ Str::words($sermon->title, 9, '...') }}
            </h3>
            <p class="text-gray-600 text-sm">
                Main text: {{ Str::words($sermon->description, 20, '...') }}<br>
                Posted by: {{ $sermon->user->name }}
            </p>
            @if ($sermon->series_title)
                <p class="text-gray-600 text-sm">
                    Series: {{ Str::words($sermon->series_title, 20, '...') }}
                </p>
            @endif
            <div class="flex gap-2 mt-2 items-center">
                @if (auth()->user() && auth()->user()->id == $sermon->user_id)
                    <a href="/sermons/{{ $sermon->id }}/edit"
                        class="text-blue-500 hover:underline text-sm bg-blue-100 p-2 rounded-md h-9 hover:bg-blue-200"
                        wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </a>
                @endif
                @if (auth()->user() && (auth()->user()->is_admin != null || auth()->user()->id == $sermon->user_id))
                    <form action="/sermons/{{ $sermon->id }}/delete" method="post" class="">
                        @csrf
                        @method('delete')
                        <button type="submit" class="flex items-center bg-red-100 hover:bg-red-200 p-2 rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class=" flex pt-2 mb-4">

        </div>
        @auth
            {{-- <audio src="{{ $sermon->audio_url }}" controls></audio> --}}
            <a href="{{ route('sermons.show', $sermon) }}" wire:navigate
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                Listen
                <svg class="w-6 h-6 text-gray-800 dark:text-white ml-2" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                        d="M20 16v-4a8 8 0 1 0-16 0v4m16 0v2a2 2 0 0 1-2 2h-2v-6h2a2 2 0 0 1 2 2ZM4 16v2a2 2 0 0 0 2 2h2v-6H6a2 2 0 0 0-2 2Z" />
                </svg>

            </a>
        @else
            <a href="{{ route('login') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                Login to listen
            </a>
        @endauth
    </div>
    <div class="sm:w-[25%] flex flex-col gap-y-2">
        @if ($sermon->ad_heading)
            <x-client_ad :sermon="$sermon" />
        @else
            <x-bc_ad :ads="$ads" />
        @endif
        <x-bc_ad :ads="$ads" />
    </div>
</article>
