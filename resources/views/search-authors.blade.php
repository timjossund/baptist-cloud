<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="max-w-2xl lg:max-w-6xl flex flex-wrap">
                <h1 class="text-5xl w-full mb-8 px-4"></h1>

                <div class="px-4 sm:px-6 lg:px-8 w-full">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h3 class="font-semibold text-3xl text-gray-900">Our Current Authors:</h3>
                        </div>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">

                                        <table class="min-w-full divide-y table-auto md:table-fixed divide-gray-300">
                                            <thead>
                                            <tr>
                                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Name</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Position</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Posts</th>
                                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Profile Link</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-300 bg-white">
                                            @foreach($users as $user)
                                                @if($user->is_author)
                                                <tr class="even:bg-gray-100">
                                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-md font-medium text-gray-900 sm:pl-0 flex items-center gap-2">
                                                        <img class="w-8 h-auto rounded-full" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                                                        <a href="{{ route('public-profile', $user) }}" class="cursor-pointer hover:underline">
                                                            {{ $user->name }}
                                                        </a>
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-md text-gray-500">
                                                        {{ $user->bio }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-md text-gray-500">
                                                        {{ $user->posts()->count() === 0 ? 'no posts yet' : $user->posts()->count() }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                        <a href="{{ route('public-profile', $user) }}" class="bg-black text-white px-3 py-2 text-md">Visit Profile</a>
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>

                                <div class="mt-6 py-2 sm:pr-6 lg:pr-8 pagination-wrapper">
                                    {{ $users->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
