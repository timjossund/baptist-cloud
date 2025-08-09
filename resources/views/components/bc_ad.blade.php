@php
 $adRandomInt = rand(0, $ads->count())
@endphp
@foreach ($ads as $ad)
    @if($ad->id === $adRandomInt)
        <a href="{{ $ad->link }}" target="_blank" class="flex flex-col items-start justify-center text-xs w-full h-2/4 p-4 rounded-lg relative" style="background-color: #f5f5f5">
            <div class="absolute bottom-0 right-0 bg-blue-800 text-white text-xs p-1">
                AD
            </div>
            <h4 class="font-black text-md mb-2 p-0">{{ Str::limit($ad->title, 25) }}</h4>
            <p class="text-sm m-0 p-0">{{ Str::limit($ad->description, 75) }}</p>
        </a>
    @endif
@endforeach
