<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="w-full lg:max-w-7xl flex border-b pb-4">
                <div class="px-4 w-3/4">
                    <div class="py-5 border-b-2 mb-2">
                        <h1 class="text-4xl">{{ $user->name }}'s Profile</h1>
                    </div>
                    <h1 class="text-3xl my-3">Recent Posts:</h1>
                    @forelse($posts as $post)
                        <x-post-item :post="$post" />
                    @empty
                        <h3>No Posts Found</h3>
                    @endforelse
                </div>
                <div class="w-1/4 px-10 flex flex-col border-l items-center">
                    <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col mt-4">
                        <h4 class="text-2xl text-center">{{ $user->name }}</h4>
                        <p class="text-md text-center">{{ $user->bio }}</p>
                        <div class="flex flex-col gap-2 mt-6">
                            @if(!$user->followers)
                                <p class="text-lg text-center">No Followers Yet</p>
                            @else
                                <p class="text-lg text-center">
                                    @if($user->followers()->count() == 1)
                                        {{ $user->followers()->count() }} Follower
                                    @else
                                        {{ $user->followers()->count() }} Followers
                                    @endif
                                </p>
                            @endif
                            @auth
                            <form action="" class="bg-blue-800 px-6 py-2 text-white rounded-lg text-center">
                                <button type="submit">Follow</button>
                            </form>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
