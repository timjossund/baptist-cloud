<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="w-full lg:max-w-7xl flex border-b pb-4 flex-wrap">
                <div class="w-full px-4 flex items-center justify-between border-b-4 pb-5 gap-3 mb-4">
                    <div class="flex flex-col md:flex-row justify-between items-center w-full">
                        <div class="flex flex-col md:flex-row items-center gap-4 pt-8 border-b-2 md:border-b-0">
                            <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                            <div class="flex flex-col">
                                <h4 class="text-2xl text-center md:text-left">{{ $user->name }}</h4>
                                <p class="text-md text-center md:text-left">{{ $user->bio }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center md:justify-start">
                            @if( auth()->user() && auth()->user()->id !== $user->id )
                                <x-follow-container :user="$user">
                                    <div class="w-full flex justify-start">
                                        <button @click="follow()" x-text="following ? 'Unfollow' : 'Follow'" :class="following ? 'bg-red-500' : 'bg-blue-800'" class="px-8 py-2 text-white rounded-lg text-center"></button>
                                    </div>
                                </x-follow-container>
                            @endif
                            @if( auth()->user()->id === $user->id )
                            <div x-data="{followersCount: {{ $followersCount }}}">
                                <p class="text-lg text-center md:text-left" x-text="followersCount === 1 ? followersCount + ' follower' : followersCount + ' followers'"></p>
                                <a href="{{ route('follower-list') }}"><x-primary-button class="w-full flex justify-center">See Followers</x-primary-button></a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="px-4 w-full">
                    {{-- <div class="py-5 border-b-2 mb-2">
                        @if( auth()->user()->id === $user->id )
                            <h1 class="text-4xl hidden md:flex">Your Profile</h1>
                        @else
                            <h1 class="text-4xl hidden md:flex">{{ $user->name }}'s Profile</h1>
                        @endif
                    </div> --}}
                    <h4 class="text-3xl my-3">Recent Posts:</h4>
                    @forelse( $posts as $post )
                        <x-post-item :post="$post" :ads="$ads" />
                    @empty
                        <h4>No Posts Found</h4>
                    @endforelse
                    <div class="mt-6 pagination-wrapper">
                        {{ $posts->links() }}
                    </div>
                </div>
                <div class="w-1/4 px-5 hidden flex-col border-l items-center">
                    <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col mt-4">
                        <h4 class="text-2xl text-center">{{ $user->name }}</h4>
                        <p class="text-md text-center">{{ $user->bio }}</p>
                        <x-follow-container :user="$user" >
                            <p class="text-lg text-center" x-text="followersCount === 1 ? followersCount + ' Follower' : followersCount + ' Followers'"></p>
                            @if( auth()->user() && auth()->user()->id !== $user->id )
                            <div class="mt-4 w-full flex justify-center">
                                <button @click="follow()" x-text="following ? 'Unfollow' : 'Follow'" :class="following ? 'bg-red-500' : 'bg-blue-800'" class="px-8 py-2 text-white rounded-lg text-center"></button>
                            </div>
                            @endif
                        </x-follow-container>
                        @if( auth()->user()->id === $user->id )
                            <a href="{{ route('follower-list') }}" wire:navigate><x-primary-button class="mt-4 w-full flex justify-center">See Followers</x-primary-button></a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
