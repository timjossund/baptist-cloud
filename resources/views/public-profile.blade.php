<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="w-full lg:max-w-6xl flex border-b pb-4">
                <div class="px-4">
                    <div class="py-5 border-b-2 mb-2">
                        <h1 class="text-5xl">{{ $user->name }}</h1>
                    </div>
                    <h1 class="text-3xl my-3">Recent Posts:</h1>
                    @foreach($user->posts as $post)
                        <x-post-item :post="$post" />
                    @endforeach
                </div>
                <div class="min-w-[320px] px-10 flex flex-col border-l">
                    <img src="{{ $user->avatar }}" alt="{{ $user->username }}" class="rounded-full h-24 w-24">
                    <div class="flex flex-col">
                        <h1 class="text-2xl">{{ $user->name }}</h1>
                        <p class="text-lg">{{ $user->bio }}</p>
                        <div class="flex flex-col gap-4 mt-4">
                            <p class="text-lg">26k followers</p>
                            <form action="" class="bg-blue-600 px-6 py-2 text-white rounded-lg text-center">
                                <button type="submit">Follow</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
