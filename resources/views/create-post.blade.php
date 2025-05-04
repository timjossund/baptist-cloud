<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl mb-8">Create a new post</h2>
                <form action="post/save-post" method="post" class="m-auto w-full max-w-2xl flex flex-col gap-4">
                    @csrf
                    <div>
                        <x-input-label class="block mb-2 text-sm font-medium text-gray-900" for="file_input">Featured Image</x-input-label>
                        <input class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" aria-describedby="file_input_help" id="file_input" type="file">
                        <p class="mt-1 text-sm text-gray-500" id="file_input_help">Supported format: JPG (MAX. 800x400px).</p>
                    </div>
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="body" :value="__('Body')" />
                        <x-textarea-input id="body" class="block border mt-1 w-full text-xl p-2" type="body" name="body" :value="old('body')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <button class="w-full bg-blue-800 text-white py-2 rounded-lg" type="submit">Save</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
