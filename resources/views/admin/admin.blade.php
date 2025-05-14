<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="max-w-2xl lg:max-w-6xl flex flex-wrap">
                <h1 class="text-5xl w-full mb-4">Admin Dashboard</h1>
                <div class="w-1/2 mt-10 p-8 bg-gray-100">
                    <h3 class="text-3xl mb-4">Site Totals:</h3>
                    <p class="text-gray-500 mb-2 text-2xl border-b pb-2 border-gray-400">
                        Total users: {{ $users->count() }}
                    </p>
                    <p class="text-gray-500 mb-2 text-2xl border-b pb-2 border-gray-400">
                        Total posts: {{ $posts->count() }}
                    </p>
                    <p class="text-gray-500 mb-2 text-2xl border-b pb-2 border-gray-400">
                        Total likes: {{ $likes->count() }}
                    </p>
                </div>
                <div class="w-1/2 mt-10 p-8">
                    <div class="bg-white sm:py-12px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
                        <div class="max-w-2xl">
                            <h3 class="text-3xl mb-4">User Management:</h3>
                            <div class="flex flex-col gap-4">
                                @foreach ($users as $user)
                                <div class="flex justify-between items-center border-b border-gray-400 py-2">
                                    <a href="{{ route('public-profile', $user) }}" class="flex justify-between items-center px-4">
                                        <div class="flex items-center">
                                            <x-user-avatar :user="$user" />
                                            <p class="ml-4 text-lg">{{ $user->name }} - Post count: {{ $user->posts->count() }}</p>
                                        </div>
                                    </a>
                                    <form action="/admin/users/{{ $user->id }}/delete" method="post"
                                        class="text-blue-500 hover:underline text-sm">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="flex items-center bg-red-100 p-2 rounded-md">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-5 text-red-500">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
