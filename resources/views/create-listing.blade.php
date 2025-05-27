<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-wrap items-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Create A New Listing</h2>
                <form action="/create-listing" method="POST" enctype="multipart/form-data" class="w-full max-w-4xl flex flex-wrap justify-between">
                    @csrf
                    {{-- Position --}}
                    <div class="w-full">
                        <x-input-label for="position" :value="__('Position:')" />
                        <x-text-input id="position" class="border mt-1 w-full text-xl p-2" type="position" name="position" :value="old('position')" autofocus />
                        <x-input-error :messages="$errors->get('position')" class="mt-2" />
                    </div>
                    {{-- Church Name --}}
                    <div class="w-full mt-4">
                        <x-input-label for="church" :value="__('Church Name:')" />
                        <x-text-input id="church" class="border mt-1 w-full text-xl p-2" type="church" name="church" :value="old('church')" autofocus />
                        <x-input-error :messages="$errors->get('church')" class="mt-2" />
                    </div>

                    <div class="w-5/12 mt-4">
                        <x-input-label for="email" :value="__('Contact Email:')" />
                        <x-text-input id="email" class="border mt-1 w-full text-xl p-2" type="email" name="email" :value="old('email')" autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    {{-- Contact Phone --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="phone" :value="__('Contact Phone:')" />
                        <x-text-input id="phone" class="border mt-1 w-full text-xl p-2" type="phone" name="phone" :value="old('phone')" autofocus />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    {{-- Facebook URL --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="facebook" :value="__('Facebook URL:')" />
                        <x-text-input id="facebook" class="border mt-1 w-full text-xl p-2" type="facebook" name="facebook" :value="old('facebook')" placeholder="https://yourfacebookurl" />
                        <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
                    </div>
                    {{-- Church Website --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="website" :value="__('Church Website:')" />
                        <x-text-input id="website" class="border mt-1 w-full text-xl p-2" type="website"
                                      name="website" :value="old('website')" placeholder="https://yourchurchwebsite" />
                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                    </div>
                    {{-- Church City --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="city" :value="__('City:')" />
                        <x-text-input id="city" class="border mt-1 w-full text-xl p-2" type="city"
                                      name="city" :value="old('city')" autofocus />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    {{-- State --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="state" :value="__('State')" />
                        <select name="state" id="state" class="border mt-1 w-full text-xl p-2">
                            <option value="">Select a State:</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                    </div>
                    {{-- Listing Body --}}
                    <div class="mt-8 w-full">
                        <label for="listingContent" class="text-lg text-gray-700">More Details: <span class="text-md text-gray-500">This text will be converted to markdown. <a class="underline text-blue-600" target="_blank" href="/learn-markdown">Learn Markdown</a></span></label>
                        <textarea class="w-full" id="listingContent" rows="10" name="content">{{ old('content') }}</textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>
                    {{-- Listing Submit --}}
                    <x-primary-button class="text-white max-w-32 flex justify-center text-center py-2 rounded-lg mt-12" type="submit">Publish</x-primary-button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
