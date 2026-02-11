<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-4xl text-gray-800 leading-tight ml-6">
                        Welcome to Baptist.Cloud Sermons
                        <p>Upload your sermons and share them with the world</p>

                        @foreach($sermons as $sermon)
                            <x-sermon-item :sermon="$sermon" />
                        @endforeach
                    </h2>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
