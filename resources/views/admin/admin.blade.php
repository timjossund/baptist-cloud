<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="max-w-2xl lg:max-w-6xl flex flex-wrap">
                <h1 class="text-5xl w-full mb-8 px-4">Admin Dashboard:</h1>
                <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 pb-20">
                    <li class="col-span-1 divide-y divide-gray-200 rounded-lg w-60 bg-white shadow-md">
                        <div class="flex items-center justify-center flex-col space-x-6 p-6">
                            <h3>Total Users</h3>
                            <h4>{{ $users->count() }}</h4>
                        </div>
                    </li>
                    <li class="col-span-1 divide-y divide-gray-200 rounded-lg w-60 bg-white shadow-md">
                        <div class="flex items-center justify-center flex-col space-x-6 p-6">
                            <h3>Total Posts</h3>
                            <h4>{{ $posts->count() }}</h4>
                        </div>
                    </li>
                    <li class="col-span-1 divide-y divide-gray-200 rounded-lg w-60 bg-white shadow-md">
                        <div class="flex items-center justify-center flex-col space-x-6 p-6">
                            <h3>Total Likes</h3>
                            <h4>{{ $likes->count() }}</h4>
                        </div>
                    </li>
                </ul>
                <div class="px-4 sm:px-6 lg:px-8 w-full">
                    <div class="sm:flex sm:items-center">
                        <div class="sm:flex-auto">
                            <h1 class="font-semibold text-3xl text-gray-900">Users</h1>
                            <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title, email and role.</p>
                        </div>
                    </div>
                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Name</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Username</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Position</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Site Role</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                            <span class="sr-only">Delete</span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                    @foreach($users as $user)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                                <a href="{{ route('public-profile', $user) }}" class="cursor-pointer hover:underline">
                                                    {{ $user->name }}
                                                </a>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->username }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->bio }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->is_author ? 'Author' : 'Subscriber' }}</td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                                <form action="/admin/users/{{ $user->id }}/delete" method="post"--}}
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
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
