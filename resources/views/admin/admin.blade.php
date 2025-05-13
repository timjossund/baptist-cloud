<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <div class="max-w-2xl lg:max-w-6xl">
                <h1 class="text-5xl">Admin Dashboard</h1>
                <div class="w-1/2 mt-10">
                    <div class="bg-white sm:py-12px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
                        <div class="max-w-2xl">
                            <h3 class="text-3xl">User Management</h3>
                            <p class="text-gray-500 mb-4">
                                Manage your users here.
                            </p>
                            <div class="flex flex-col gap-4">
                                @foreach ($users as $user)
                                    <a href="{{ route('public-profile', $user) }}" class="flex justify-between items-center border-b py-2">
                                        <div class="flex items-center">
                                            <x-user-avatar :user="$user" />
                                            <p class="ml-4 text-lg">{{ $user->name }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    </div>
</x-app-layout>