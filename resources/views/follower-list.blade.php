<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white py-12 md:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col ">
            <div class="flex justify-between py-4 items-center">
                <h1 class="text-4xl">Your Followers: {{ auth()->user()->followers()->count() }}</h1>
                <a href="{{ route('public-profile', auth()->user()) }}"><x-primary-button class="flex justify-center">Back To Profile</x-primary-button></a>
            </div>
            <div class="flex flex-col border-t-4 border-gray-200 py-4">
                @forelse( auth()->user()->followers as $follower )
                    <a href="{{ route('public-profile', $follower) }}" wire:navigate class="flex items-center border-b-2 border-gray-200 p-2 first:border-t-2 min-w-56 justify-between">
                        <div class="flex items-center">
                            <img src="{{ $follower->avatar }}" alt="{{ $follower->username }}" class="rounded-full h-10 w-10">
                            <p class="ml-2 text-md">{{ $follower->name }}</p>
                        </div>
                        <p class="ml-2 text-md mr-2">Username: {{ $follower->username }}</p>
                        <p class="ml-2 text-md mr-2">Position: {{ $follower->bio }}</p>
                    </a>
                @empty
                    <h6 class="text-sm text-center">- No Followers To List -</h6>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>