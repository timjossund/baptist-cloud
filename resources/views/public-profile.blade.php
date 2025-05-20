<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="w-full lg:max-w-7xl flex border-b pb-4 flex-wrap">
                <div class="w-full px-4 flex items-center border-b pb-5 md:hidden gap-3">
                    <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col mt-4">
                        <h4 class="text-2xl text-left">{{ $user->name }}</h4>
                        <p class="text-md text-left">{{ $user->bio }}</p>
                        <x-follow-container :user="$user" >
                            <p class="text-lg text-left" x-text="followersCount === 1 ? followersCount + ' follower' : followersCount + ' followers'"></p>

                            @if( auth()->user() && auth()->user()->id !== $user->id )
                                <div class="mt-4 w-full flex justify-start">
                                    <button @click="follow()" x-text="following ? 'unfollow' : 'follow'" :class="following ? 'bg-red-500' : 'bg-blue-800'" class="px-8 py-2 text-white rounded-lg text-center"></button>
                                </div>
                            @endif
                        </x-follow-container>
                    </div>
                </div>
                <div class="px-4 w-full md:w-3/4">
                    <div class="py-5 border-b-2 mb-2">
                        @if( auth()->user()->id === $user->id )
                            <h1 class="text-4xl hidden md:flex">Your Profile</h1>
                        @else
                            <h1 class="text-4xl hidden md:flex">{{ $user->name }}'s Profile</h1>
                        @endif
                    </div>
                    <h4 class="text-3xl my-3">Recent Posts:</h4>
                    @forelse( $posts as $post )
                        <x-post-item :post="$post" />
                    @empty
                        <h4>No Posts Found</h4>
                    @endforelse
                </div>
                <div class="w-1/4 px-5 hidden flex-col border-l items-center md:flex">
                    <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col mt-4">
                        <h4 class="text-2xl text-center">{{ $user->name }}</h4>
                        <p class="text-md text-center">{{ $user->bio }}</p>
                        <x-follow-container :user="$user" >
                            <p class="text-lg text-center" x-text="followersCount === 1 ? followersCount + ' Follower' : followersCount + ' Followers'"></p>

                            @if( auth()->user() && auth()->user()->id !== $user->id )
                                <div class="mt-4 w-full flex justify-center">
                                    <button @click="follow()" x-text="following ? 'unfollow' : 'follow'" :class="following ? 'bg-red-500' : 'bg-blue-800'" class="px-8 py-2 text-white rounded-lg text-center"></button>
                                </div>
                            @endif
                        </x-follow-container>
                        @if( auth()->user() && auth()->user()->id === $user->id )
                        <div class="flex flex-col mt-4 h-96 px-3 overflow-y-scroll w-56 max-w-56">
                            @forelse( $user->followers as $follower )
                                <a href="{{ route('public-profile', $follower) }}" class="flex items-center border-b-2 border-gray-200 py-2 first:border-t-2">
                                    <img src="{{ $follower->avatar }}" alt="{{ $follower->username }}" class="rounded-full h-10 w-10">
                                    <p class="ml-2 text-md">{{ $follower->username }}</p>
                                </a>
                            @empty
                                <h6 class="text-sm">- No Followers To List -</h6>
                            @endforelse
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
