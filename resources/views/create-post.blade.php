<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center" x-data="{ isSaving: false }">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl mx-auto w-full">Create A New Article</h2>

                <div class="flex flex-col lg:flex-row gap-8 w-full max-w-6xl mx-auto items-start">
                    {{-- Form: 2/3 Section Width --}}
                    <div class="w-full lg:w-2/3">
                        <form action="/post/create-post" method="POST" enctype="multipart/form-data" @submit="isSaving = true" class="w-full flex flex-col gap-4">
                            @csrf
                            {{-- Post Image --}}
                            <div>
                                <x-input-label class="block mb-2" for="image">Featured Image</x-input-label>
                                <input class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" aria-describedby="file_input_help" id="image" type="file" name="image">
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            </div>
                            {{-- Post Title --}}
                            <div>
                                <x-input-label for="title" :value="__('Title:')" />
                                <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="old('title')" autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>
                            {{-- Post Category --}}
                            <div>
                                <x-input-label for="category_id" :value="__('Category:')" />
                                <select name="category_id" id="category_id" class="block border mt-1 w-full text-xl p-2">
                                    <option value="">Select a Category:</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>
                            {{-- Post Tags --}}
                            <div>
                                <x-input-label for="tags" :value="__('Tags: (comma separated)')"/>
                                <input type="text" name="tags" id="tags" class="block border mt-1 w-full text-xl p-2" value="{{ old('tags') }}"
                                       wire:model="tags">
                                <x-input-error :messages="$errors->get('tags')" class="mt-2"/>
                            </div>
                            {{-- Post Body --}}
                            <div class="mt-2 w-full flex flex-col">
                                <label for="content" class="text-lg text-gray-700 mb-2">Body Content: <span class="text-md text-gray-500">This text will be converted to markdown.</span></label>
                                <textarea rows="10" id="content" name="content">{{ old('content') }}</textarea>
                                <div id="bodycontent">{!! old('content') !!}</div>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>
                            @if (auth()->user()->subscribed() || auth()->user()->is_author || auth()->user()->is_admin)
                                <div class="flex flex-col gap-4 mt-4 bg-gray-100 p-6 rounded-lg">
                                    {{-- Post Ad Heading --}}
                                    <div>
                                        <h4>Add an advertisement to the top of the post. (Optional)</h4>
                                        <x-input-label for="ad_heading" :value="__('Ad Heading: Limit 25 Characters')"/>
                                        <x-text-input id="ad_heading" class="block border mt-1 w-full text-xl p-2" type="ad_heading"
                                                      name="ad_heading" :value="old('ad_heading')" wire:model="ad_heading"/>
                                        <x-input-error :messages="$errors->get('ad_heading')" class="mt-2"/>
                                    </div>
                                    {{-- Post Ad Description --}}
                                    <div>
                                        <x-input-label for="ad_description" :value="__('Ad Description: Limit 75 Characters')"/>
                                        <x-text-input id="ad_description" class="block border mt-1 w-full text-xl p-2" type="ad_description"
                                                      name="ad_description" :value="old('ad_description')" wire:model="ad_description"/>
                                        <x-input-error :messages="$errors->get('ad_description')" class="mt-2"/>
                                    </div>
                                    {{-- Post Ad Link --}}
                                    <div>
                                        <x-input-label for="ad_link" :value="__('Ad Link: please use a full link - including https://')"/>
                                        <x-text-input id="ad_link" class="block border mt-1 w-full text-xl p-2" type="ad_link" name="ad_link"
                                                      :value="old('ad_link')" wire:model="ad_link" placeholder="https://example.com"/>
                                        <x-input-error :messages="$errors->get('ad_link')" class="mt-2"/>
                                    </div>
                                </div>
                            @endif
                            {{-- Post Submit --}}
                            <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg mt-2" type="submit" ::disabled="isSaving">
                                <span x-show="!isSaving">Save Draft</span>
                                <span x-show="isSaving" x-cloak class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
                            </x-primary-button>
                        </form>
                    </div>

                    {{-- Markdown Examples: 1/3 Section Width --}}
                    <div class="w-full lg:w-1/3 lg:sticky lg:top-6">
                        <x-markdown-guide />
                    </div>
                </div>
            </div>
        </div>

        {{-- Saving Overlay --}}
        <div x-show="isSaving" x-cloak class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/40 backdrop-blur-sm transition-all">
            <div class="bg-white p-8 rounded-2xl shadow-xl flex flex-col items-center gap-4 text-center max-w-sm border border-gray-100">
                <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Saving Article</h3>
                    <p class="text-sm text-gray-500 mt-1">Processing your featured image and saving draft... Please wait.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
