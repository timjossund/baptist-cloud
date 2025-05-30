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
                                <x-users-table :users="$users" />
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
