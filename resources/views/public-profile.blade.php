<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="w-full lg:max-w-7xl flex border-b pb-4">
                <div class="px-4 w-3/4">
                    <div class="py-5 border-b-2 mb-2">
                        @if( auth()->user()->id === $user->id)
                            <h1 class="text-4xl">Your Profile</h1>
                        @else
                            <h1 class="text-4xl">{{ $user->name }}'s Profile</h1>
                        @endif
                    </div>
                    <h4 class="text-3xl my-3">Recent Posts:</h4>
                    @forelse($posts as $post)
                        <x-post-item :post="$post" />
                    @empty
                        <h4>No Posts Found</h4>
                    @endforelse
                </div>
                <div class="w-1/4 px-10 flex flex-col border-l items-center">
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col mt-4">
                        <h4 class="text-2xl text-center">{{ $user->name }}</h4>
                        <p class="text-md text-center">{{ $user->bio }}</p>
                        <x-follow-container :user="$user" >
                            <p class="text-lg text-center" x-text="followersCount === 1 ? followersCount + ' follower' : followersCount + ' followers'"></p>

                            @if(auth()->user() && auth()->user()->id !== $user->id )
                                <div class="mt-4 w-full flex justify-center">
                                    <button @click="follow()" x-text="following ? 'unfollow' : 'follow'" :class="following ? 'bg-red-500' : 'bg-blue-800'" class="px-8 py-2 text-white rounded-lg text-center"></button>
                                </div>
                            @endif
                        </x-follow-container>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
