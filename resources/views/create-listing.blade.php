<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl mx-auto w-full">Create A New Listing</h2>

                <div class="flex flex-col lg:flex-row gap-8 w-full max-w-6xl mx-auto items-start">
                    {{-- Form: 2/3 Section Width --}}
                    <div class="w-full lg:w-2/3">
                        <form action="/create-listing" method="POST" enctype="multipart/form-data" class="w-full flex flex-wrap justify-between">
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
                                <x-text-input id="church" class="border mt-1 w-full text-xl p-2" type="church" name="church" :value="old('church')" />
                                <x-input-error :messages="$errors->get('church')" class="mt-2" />
                            </div>

                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="email" :value="__('Contact Email:')" />
                                <x-text-input id="email" class="border mt-1 w-full text-xl p-2" type="email" name="email" :value="old('email')" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            {{-- Contact Phone --}}
                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="phone" :value="__('Contact Phone:')" />
                                <x-text-input id="phone" class="border mt-1 w-full text-xl p-2" type="phone" name="phone" :value="old('phone')" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                            {{-- Facebook URL --}}
                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="facebook" :value="__('Facebook URL:')" />
                                <x-text-input id="facebook" class="border mt-1 w-full text-xl p-2" type="facebook" name="facebook" :value="old('facebook')" placeholder="https://yourfacebookurl" />
                                <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
                            </div>
                            {{-- Church Website --}}
                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="website" :value="__('Church Website:')" />
                                <x-text-input id="website" class="border mt-1 w-full text-xl p-2" type="website"
                                              name="website" :value="old('website')" placeholder="https://yourchurchwebsite.com" />
                                <x-input-error :messages="$errors->get('website')" class="mt-2" />
                            </div>
                            {{-- Church City --}}
                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="city" :value="__('City:')" />
                                <x-text-input id="city" class="border mt-1 w-full text-xl p-2" type="city"
                                              name="city" :value="old('city')" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            {{-- State --}}
                            <div class="w-full sm:w-[48%] mt-4">
                                <x-input-label for="state" :value="__('State')" />
                                <select name="state" id="state" class="border mt-1 w-full text-xl p-2">
                                    <option value="">Select a State:</option>
                                    <option value="AL" @selected(old('state') == "AL")>Alabama</option>
                                    <option value="AK" @selected(old('state') == "AK")>Alaska</option>
                                    <option value="AZ" @selected(old('state') == "AZ")>Arizona</option>
                                    <option value="AR" @selected(old('state') == "AR")>Arkansas</option>
                                    <option value="CA" @selected(old('state') == "CA")>California</option>
                                    <option value="CO" @selected(old('state') == "CO")>Colorado</option>
                                    <option value="CT" @selected(old('state') == "CT")>Connecticut</option>
                                    <option value="DE" @selected(old('state') == "DE")>Delaware</option>
                                    <option value="DC" @selected(old('state') == "DC")>District Of Columbia</option>
                                    <option value="FL" @selected(old('state') == "FL")>Florida</option>
                                    <option value="GA" @selected(old('state') == "GA")>Georgia</option>
                                    <option value="HI" @selected(old('state') == "HI")>Hawaii</option>
                                    <option value="ID" @selected(old('state') == "ID")>Idaho</option>
                                    <option value="IL" @selected(old('state') == "IL")>Illinois</option>
                                    <option value="IN" @selected(old('state') == "IN")>Indiana</option>
                                    <option value="IA" @selected(old('state') == "IA")>Iowa</option>
                                    <option value="KS" @selected(old('state') == "KS")>Kansas</option>
                                    <option value="KY" @selected(old('state') == "KY")>Kentucky</option>
                                    <option value="LA" @selected(old('state') == "LA")>Louisiana</option>
                                    <option value="ME" @selected(old('state') == "ME")>Maine</option>
                                    <option value="MD" @selected(old('state') == "MD")>Maryland</option>
                                    <option value="MA" @selected(old('state') == "MA")>Massachusetts</option>
                                    <option value="MI" @selected(old('state') == "MI")>Michigan</option>
                                    <option value="MN" @selected(old('state') == "MN")>Minnesota</option>
                                    <option value="MS" @selected(old('state') == "MS")>Mississippi</option>
                                    <option value="MO" @selected(old('state') == "MO")>Missouri</option>
                                    <option value="MT" @selected(old('state') == "MT")>Montana</option>
                                    <option value="NE" @selected(old('state') == "NE")>Nebraska</option>
                                    <option value="NV" @selected(old('state') == "NV")>Nevada</option>
                                    <option value="NH" @selected(old('state') == "NH")>New Hampshire</option>
                                    <option value="NJ" @selected(old('state') == "NJ")>New Jersey</option>
                                    <option value="NM" @selected(old('state') == "NM")>New Mexico</option>
                                    <option value="NY" @selected(old('state') == "NY")>New York</option>
                                    <option value="NC" @selected(old('state') == "NC")>North Carolina</option>
                                    <option value="ND" @selected(old('state') == "ND")>North Dakota</option>
                                    <option value="OH" @selected(old('state') == "OH")>Ohio</option>
                                    <option value="OK" @selected(old('state') == "OK")>Oklahoma</option>
                                    <option value="OR" @selected(old('state') == "OR")>Oregon</option>
                                    <option value="PA" @selected(old('state') == "PA")>Pennsylvania</option>
                                    <option value="RI" @selected(old('state') == "RI")>Rhode Island</option>
                                    <option value="SC" @selected(old('state') == "SC")>South Carolina</option>
                                    <option value="SD" @selected(old('state') == "SD")>South Dakota</option>
                                    <option value="TN" @selected(old('state') == "TN")>Tennessee</option>
                                    <option value="TX" @selected(old('state') == "TX")>Texas</option>
                                    <option value="UT" @selected(old('state') == "UT")>Utah</option>
                                    <option value="VT" @selected(old('state') == "VT")>Vermont</option>
                                    <option value="VA" @selected(old('state') == "VA")>Virginia</option>
                                    <option value="WA" @selected(old('state') == "WA")>Washington</option>
                                    <option value="WV" @selected(old('state') == "WV")>West Virginia</option>
                                    <option value="WI" @selected(old('state') == "WI")>Wisconsin</option>
                                    <option value="WY" @selected(old('state') == "WY")>Wyoming</option>
                                </select>
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>
                            {{-- Listing Body --}}
                            <div class="mt-8 w-full">
                                <label for="listingContent" class="text-lg text-gray-700">More Details: <span class="text-md text-gray-500">This text will be converted to markdown.</span></label>
                                <textarea class="w-full" id="listingContent" rows="10" name="content">{{ old('content') }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>
                            {{-- Listing Submit --}}
                            <x-primary-button class="text-white max-w-32 flex justify-center text-center py-2 rounded-lg mt-8" type="submit">Publish</x-primary-button>
                        </form>
                    </div>

                    {{-- Markdown Examples: 1/3 Section Width --}}
                    <div class="w-full lg:w-1/3 lg:sticky lg:top-6">
                        <x-markdown-guide />
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
