<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="hidden sm:block">
                    <nav class="flex space-x-4 p-4" aria-label="Tabs">
                        <a href="/dashboard" class="{{ Request::segment(3) == "" ? "rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700" : "rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" }} rounded-lg flex justify-center items-center" aria-current="page">All</a>
                        @foreach ($categories as $category)
                        <a href="/dashboard/{{ $category['title'] }}" class="inline-block px-4 py-3 {{ Request::segment(3) == $category['title'] ? "rounded-md bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700" : "rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" }} rounded-lg" aria-current="page">{{ $category['title'] }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
