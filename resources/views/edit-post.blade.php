<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center" x-data="{publish: false}">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Publish Your Post</h2>
                    <form :action="publish ? '/post/{{ $post->slug }}/publish' : '/post/{{ $post->slug }}'" method="post" enctype="multipart/form-data" class="m-auto w-full max-w-6xl flex flex-col gap-4">
                    @csrf
                    @method('patch')B
                    {{-- Post Image --}}
                    <div class="mb-56 w-full h-48 bg-cover bg-center rounded-lg">
                        <x-input-label class="block mb-2" for="image">Current Featured Image</x-input-label>
                        <img src="{{ $post->image }}" alt="">
                    </div>
                    <div>
                        <x-input-label class="block mb-2" for="image">
                            Change Featured Image
                        </x-input-label>
                        <input
                            class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                            aria-describedby="file_input_help" id="image" type="file" name="image">
                        <p class="mt-1 text-sm text-gray-500" id="file_input_help"></p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>
                    {{-- Post Title --}}
                    <div>
                        <x-input-label for="title" :value="__('Title:')" />
                        <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title"
                            name="title" value="{{ $post->title }}" autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    {{-- Post Category --}}
                    <div>
                        <x-input-label for="category_id" :value="__('Category:')" />
                        <select name="category_id" id="category_id" class="block border mt-1 w-full text-xl p-2">
                            <option value="">Select a Category:</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($post->category_id == $category->id)>{{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>
                    {{-- Post Body --}}
                    <div class="mt-2">
                        <label for="content" class="text-lg text-gray-700">Body Content</label>
                        <textarea id="content" class="hidden" name="content">{!! $post->content !!}</textarea>
                        <div id="bodycontent">{!! $post->content !!}</div>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>
                    {{-- Post Submit --}}
                    <div class="flex gap-4">
                        <x-primary-button class="text-white max-w-32 flex justify-center text-center py-2 rounded-lg" type="submit">
                            Save
                        </x-primary-button>
                        <div class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" @click="publish = true">
                            Publish
                        </div>
                    </div>
                    <div x-show="publish" class="flex gap-4 fixed justify-center items-center w-full h-full z-50 bg-black bg-opacity-20 top-0 left-0">
                        <div class="bg-white flex flex-col justify-center items-center text-center py-20 px-40 rounded-lg gap-4">
                            <h3>Are you sure you want to publish?</h3>
                            <x-primary-button class="text-white max-w-32 flex justify-center text-center py-2 rounded-lg" type="submit">
                                Publish
                            </x-primary-button>
                        </div>

                    </div>
                </form>
                <a href="{{ route('public-profile', $post->user) }}" class="text-red-500">Cancel Edit</a>
            </div>
        </div>
    </section>
</x-app-layout>
