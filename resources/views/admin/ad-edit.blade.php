<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-col items-center justify-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Edit Ad</h2>
                <form action="/update-ad/{{$ad->id}}" method="post" class="m-auto w-full max-w-6xl flex flex-col gap-4">
                    @csrf
                    @method('patch')
                    {{-- Ad Title --}}

                    <div class="flex flex-col gap-4 mt-4 bg-gray-100 p-6 rounded-lg">
                        {{--    Post Ad Heading --}}
                        <div>
                            <x-input-label for="title" :value="__('Ad Title:')" />
                            <x-text-input id="title" class="block border mt-1 w-full text-xl p-2" type="title" name="title" :value="$ad->title"/>
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        {{--    Post Ad Description --}}
                        <div>
                            <x-input-label for="description" :value="__('Ad Desription:')" />
                            <x-text-input id="description" class="block border mt-1 w-full text-xl p-2" type="description" name="description" :value="$ad->description"/>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        {{--    Post Ad Link --}}
                        <div>
                            <x-input-label for="link" :value="__('Ad Link:')" />
                            <x-text-input id="link" class="block border mt-1 w-full text-xl p-2" type="link" name="link" :value="$ad->link" placeholder="https://example.com"/>
                            <x-input-error :messages="$errors->get('link')" class="mt-2" />
                        </div>
                    </div>
                    <div class="flex justify-between mt-4">
                        {{-- Post Submit --}}
                        <x-primary-button class="text-white max-w-52 flex justify-center text-center py-2 rounded-lg" type="submit">Save Ad</x-primary-button>
                        <a href="/create-ad" class="text-blue-500 max-w-52 flex justify-center text-center font-black py-2 rounded-lg">Cancel Edit</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
