<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-4xl text-gray-800 leading-tight ml-6">
                        Create Sermon
                    </h2>
                    <form action="{{ route('sermons.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4 p-6 flex flex-col gap-4">
                        @csrf
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="image">Sermon Graphic (Optional)</label>
                            <input type="file" name="image" id="image">
                        </div>
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="series_title">Sermon Series (Optional)</label>
                            <input type="text" name="series_title" id="series_title">
                        </div>
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="title">Title (Required)</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="description">Description (Required)</label>
                            <textarea name="description" id="description" cols="30" rows="10" required></textarea>
                        </div>
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="audio">Sermon Audio (Required)</label>
                            <input type="file" name="audio" id="audio" required>
                        </div>
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="published_at">Sermon Date (Required)</label>
                            <input type="date" name="published_at" id="published_at" required>
                        </div>
                        <button type="submit"
                            class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-300 ease-in-out uppercase font-medium w-1/4">
                            Create Sermon
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
