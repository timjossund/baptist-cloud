<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-4xl text-gray-800 leading-tight ml-6">
                        Edit Sermon
                    </h2>
                    <form action="{{ route('sermons.update', $sermon) }}" method="POST" enctype="multipart/form-data"
                        class="space-y-4 p-6 flex flex-col gap-4">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="image_url">Sermon Graphic (Optional)</label>
                            <input type="file" name="image_url" id="image_url">
                        </div>
                        @error('image_url')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="series_title">Sermon Series (Optional)</label>
                            <input type="text" name="series_title" id="series_title"
                                value="{{ $sermon->series_title }}">
                        </div>
                        @error('series_title')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="title">Title (Required)</label>
                            <input type="text" name="title" id="title" required value="{{ $sermon->title }}">
                        </div>
                        @error('title')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="description">Main Scripture Text (Required)</label>
                            <input type="text" name="description" id="description" required
                                value="{{ $sermon->description }}">
                        </div>
                        @error('description')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="audio_url">Sermon Audio</label>
                            <input type="file" name="audio_url" id="audio_url" value="{{ $sermon->audio_url }}">
                        </div>
                        @error('audio')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="mb-4 flex flex-col gap-2">
                            <label for="published_at">Sermon Date</label>
                            <input type="date" name="published_at" id="published_at"
                                value="{{ $sermon->published_at }}">
                        </div>
                        @error('published_at')
                            <div class="text-red-500 mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                        <button type="submit"
                            class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-300 ease-in-out uppercase font-medium w-1/4">
                            Update Sermon
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
