<form wire:submit="store" class="m-auto w-full max-w-6xl flex flex-col gap-4">
    @csrf
    {{-- Post Image --}}
    <div class="relative">
        @if ($image)
            <img src="{{ $image->temporaryUrl() }}" alt="" class="w-full rounded-xl object-cover mb-12 aspect-[3/1]">
        @endif
        <x-input-label class="block mb-2" for="image">Featured Image: this will be cropped to a 3:1 ratio</x-input-label>
        <input class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:border-indigo-500" aria-describedby="file_input_help" id="image" type="file" name="image" wire:model="image" autofocus>
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>
    {{-- Post Title --}}
    <div>
        <x-input-label for="title" :value="__('Title:')" />
        <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="old('title')" wire:model="title"/>
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>
    {{-- Post Category --}}
    <div>
        <x-input-label for="category_id" :value="__('Category:')" />
        <select name="category_id" id="category_id" class="block border mt-1 w-full text-xl p-2" wire:model="category_id">
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
        <x-input-label for="tags" :value="__('Tags: (comma separated)')" />
        <input type="text" name="tags" id="tags" class="block border mt-1 w-full text-xl p-2" value="{{ old('tags') }}" wire:model="tags">
        <x-input-error :messages="$errors->get('tags')" class="mt-2" />
    </div>
    {{-- Post Body --}}
    <div class="mt-2 w-full flex flex-col">
        <label for="content" class="text-lg text-gray-700 mb-2">Body Content:
            <span class="text-md text-gray-500">This text will be converted to markdown. <a class="underline text-blue-600" target="_blank" href="/learn-markdown">Learn Markdown</a></span>
        </label>
        <textarea rows="10" id="bodyContent" name="bodyContent" wire:model="content">{{ old('content') }}</textarea>
{{--        <div id="body_content" class="min-h-32">{!! old('content') !!}</div>--}}
{{--        <livewire:jodit-text-editor wire:model="content" />--}}
        <x-input-error :messages="$errors->get('content')" class="mt-2" />
    </div>
    <div class="flex flex-col gap-4 mt-4 bg-gray-100 p-6 rounded-lg">
{{--    Post Ad Heading --}}
        <div>
            <h4>Add and advertisement to the top of the post. (Optional)</h4>
            <x-input-label for="ad_heading" :value="__('Ad Heading: Limit 25 Characters')" />
            <x-text-input id="ad_heading" class="block border mt-1 w-full text-xl p-2" type="ad_heading" name="ad_heading" :value="old('ad_heading')" wire:model="ad_heading"/>
            <x-input-error :messages="$errors->get('ad_heading')" class="mt-2" />
        </div>
        {{--    Post Ad Description --}}
        <div>
            <x-input-label for="ad_description" :value="__('Ad Description: Limit 75 Characters')" />
            <x-text-input id="ad_description" class="block border mt-1 w-full text-xl p-2" type="ad_description" name="ad_description" :value="old('ad_description')" wire:model="ad_description"/>
            <x-input-error :messages="$errors->get('ad_heading')" class="mt-2" />
        </div>
        {{--    Post Ad Link --}}
        <div>
            <x-input-label for="ad_link" :value="__('Ad Link:')" />
            <x-text-input id="ad_link" class="block border mt-1 w-full text-xl p-2" type="ad_link" name="ad_link" :value="old('ad_link')" wire:model="ad_link" placeholder="https://example.com"/>
            <x-input-error :messages="$errors->get('ad_heading')" class="mt-2" />
        </div>
    </div>
    {{-- Post Submit --}}
    <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg" type="submit">Save Draft</x-primary-button>
</form>
