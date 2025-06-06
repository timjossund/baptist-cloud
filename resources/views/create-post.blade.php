<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Create A New Article</h2>
                <livewire:post-form />

{{--                <form action="/post/create-post" method="POST" enctype="multipart/form-data" class="m-auto w-full max-w-6xl flex flex-col gap-4">--}}
{{--                    @csrf--}}
{{--                    --}}{{-- Post Image --}}
{{--                    <div>--}}
{{--                        <x-input-label class="block mb-2" for="image">Featured Image</x-input-label>--}}
{{--                        <input class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" aria-describedby="file_input_help" id="image" type="file" name="image">--}}
{{--                        <x-input-error :messages="$errors->get('image')" class="mt-2" />--}}
{{--                    </div>--}}
{{--                    --}}{{-- Post Title --}}
{{--                    <div>--}}
{{--                        <x-input-label for="title" :value="__('Title:')" />--}}
{{--                        <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="old('title')" autofocus />--}}
{{--                        <x-input-error :messages="$errors->get('title')" class="mt-2" />--}}
{{--                    </div>--}}
{{--                    --}}{{-- Post Category --}}
{{--                    <div>--}}
{{--                        <x-input-label for="category_id" :value="__('Category:')" />--}}
{{--                        <select name="category_id" id="category_id" class="block border mt-1 w-full text-xl p-2">--}}
{{--                            <option value="">Select a Category:</option>--}}
{{--                            @foreach ($categories as $category)--}}
{{--                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->title }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />--}}
{{--                    </div>--}}
{{--                    --}}{{-- Post Body --}}
{{--                    <div class="mt-2 w-full flex flex-col">--}}
{{--                        <label for="content" class="text-lg text-gray-700 mb-2">Body Content: <span class="text-md text-gray-500">This text will be converted to markdown. <a class="underline text-blue-600" target="_blank" href="https://www.markdownguide.org/cheat-sheet/">Learn Markdown</a></span></label>--}}
{{--                        <textarea  rows="10" id="content" name="content">{{ old('content') }}</textarea>--}}
{{--                        <div id="bodycontent">{!! old('content') !!}</div>--}}
{{--                        <x-input-error :messages="$errors->get('content')" class="mt-2" />--}}
{{--                    </div>--}}
{{--                    --}}{{-- Post Submit --}}
{{--                    <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg" type="submit">Save Draft</x-primary-button>--}}
{{--                </form>--}}
            </div>
        </div>
    </section>
</x-app-layout>
