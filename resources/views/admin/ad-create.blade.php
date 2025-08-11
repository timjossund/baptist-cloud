<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Create A New Ad</h2>
                <form :action="/ad-create" method="POST" class="m-auto w-full max-w-6xl flex flex-col gap-4">
                    @csrf
                    {{-- Ad Title --}}

                    <div class="flex flex-col gap-4 mt-4 bg-gray-100 p-6 rounded-lg">
                        {{--    Post Ad Heading --}}
                        <div>
                            <x-input-label for="title" :value="__('Ad Title:')" />
                            <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="old('title')"/>
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        {{--    Post Ad Description --}}
                        <div>
                            <x-input-label for="description" :value="__('Ad Desription:')" />
                            <x-text-input id="description" class="block border mt-1 w-full text-xl p-2" type="description" name="description" :value="old('description')"/>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        {{--    Post Ad Link --}}
                        <div>
                            <x-input-label for="link" :value="__('Ad Link:')" />
                            <x-text-input id="link" class="block border mt-1 w-full text-xl p-2" type="link" name="link" :value="old('link')" placeholder="https://example.com"/>
                            <x-input-error :messages="$errors->get('link')" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex justify-between mt-4">
                    {{-- Post Submit --}}
                    <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg" type="submit">Save Ad</x-primary-button>
                    <a href="/admin" class="text-blue-500 max-w-52 flex justify-center text-center font-black py-2 rounded-lg">Cancel AD</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Current Ads</h2>
                <div class="grid w-full grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($ads as $ad)
                    <div class="h-[200px]">
                        <a href="{{ $ad->link }}" target="_blank" class="flex flex-col items-start justify-center text-xs w-full h-2/4 p-4 rounded-lg relative" style="background-color: #f5f5f5">
                            <div class="absolute bottom-0 right-0 bg-blue-800 text-white text-xs p-1">
                                AD
                            </div>
                            <h4 class="font-black text-md mb-2 p-0">{{ Str::limit($ad->title, 25) }}</h4>
                            <p class="text-sm m-0 p-0">{{ Str::limit($ad->description, 75) }}</p>
                        </a>
                        <div class="flex justify-between p-4 border-black border-b-2">
                            <a href="/edit-ad/{{$ad->id}}" class="text-blue-500 max-w-52 flex justify-center text-center font-black py-2 rounded-lg">Edit AD</a>
                            <form action="/delete-ad/{{$ad->id}}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 max-w-52 flex justify-center text-center font-black py-2 rounded-lg">Delete AD</button>
                            </form>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
