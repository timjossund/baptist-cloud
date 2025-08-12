<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5" x-data="{showForm: true}">
        <div class="bg-white py-6 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col ">
            <div class="w-full rounded-lg shadow-sm sm:rounded-lg flex flex-col justify-start items-start">
                <div class="w-full mt-10">
                    <h2>Report This Article:</h2>
                    <p>This form will submit your name and email and your report.</p>
                    <form action="/report/{{ $post->id }}" method="post" class="mt-4 flex flex-col gap-4">
                        @csrf
                        <input type="hidden" name="reporter_name" id="reporter_name" value="{{ auth()->user()->name }}">
                        <x-input-error :messages="$errors->get('reporter_name')" class="mt-2" />
                        <input type="hidden" name="reporter_email" id="reporter_email" value="{{ auth()->user()->email }}">
                        <x-input-error :messages="$errors->get('reporter_email')" class="mt-2" />
                        <input type="hidden" name="post_id" id="post_id" value="{{ $post->id }}">
                        <x-input-error :messages="$errors->get('post_id')" class="mt-2" />

                        <input type="hidden" name="post_title" id="post_title" value="{{ $post->title }}">
                        <x-input-error :messages="$errors->get('post_title')" class="mt-2" />
                        <input type="hidden" name="post_slug" id="post_slug" value="{{ $post->slug }}">
                        <x-input-error :messages="$errors->get('post_slug')" class="mt-2" />
                        <input type="hidden" name="username" id="username" value="{{ $post->user->username }}">
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        <label for="description">Report: Please be detailed.</label>
                        <x-textarea-input type="text" name="description" id="description" :value="old('description')" />
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        <div class="flex justify-between gap-4">
                            <button class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" type="submit">Submit</button>
                            <a href="{{ url()->previous() }}" class="cursor-pointer h-full flex justify-center items-center">Cancel Report</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
