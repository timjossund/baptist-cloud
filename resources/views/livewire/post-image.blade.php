<div>
    @if ($image)
        <img src="{{ $image->temporaryUrl() }}" alt="" class="w-full rounded-xl object-cover mb-12 aspect-[16/9]">
    @endif
    <x-input-label class="block mb-2" for="image">Featured Image: this will be cropped to a 16:9 ratio</x-input-label>
    <input class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:border-indigo-500" aria-describedby="file_input_help" id="image" type="file" name="image" wire:model="image" autofocus>
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
</div>
