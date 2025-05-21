<x-app-layout>
    <section class="container mx-auto px-6 py-8 flex justify-center">
        <div class="max-w-7xl mx-auto px-5 w-full">
            <div class="bg-white flex flex-wrap items-center sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg">
                <h2 class="text-4xl font-bold mb-8 max-w-6xl w-full">Edit Listing</h2>
                <form action="/position/{{$listing->id}}/save" method="POST" enctype="multipart/form-data" class="w-full max-w-4xl flex flex-wrap justify-between">
                    @csrf
                    @method('PATCH')
                    {{-- Position --}}

                    <div class="w-full">
                        <x-input-label for="position" :value="__('Position:')" />
                        <x-text-input id="position" class="border mt-1 w-full text-xl p-2" type="position" name="position" value="
                        {{ $listing->position }}
                        " autofocus />
                        <x-input-error :messages="$errors->get('position')" class="mt-2" />
                    </div>
                    {{-- Church Name --}}
                    <div class="w-full mt-4">
                        <x-input-label for="church" :value="__('Church Name:')" />
                        <x-text-input id="church" class="border mt-1 w-full text-xl p-2" type="church" name="church" value="{{ $listing->church}}" />
                        <x-input-error :messages="$errors->get('church')" class="mt-2" />
                    </div>

                    <div class="w-5/12 mt-4">
                        <x-input-label for="email" :value="__('Contact Email:')" />
                        <x-text-input id="email" class="border mt-1 w-full text-xl p-2" type="email" name="email" value="{{$listing->email}}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    {{-- Contact Phone --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="phone" :value="__('Contact Phone:')" />
                        <x-text-input id="phone" class="border mt-1 w-full text-xl p-2" type="phone" name="phone" value="{{$listing->phone}}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    {{-- Facebook URL --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="facebook" :value="__('Facebook Profile URL:')" />
                        <x-text-input id="facebook" class="border mt-1 w-full text-xl p-2" type="facebook" name="facebook" value="{{$listing->facebook}}" />
                        <x-input-error :messages="$errors->get('facebook')" class="mt-2" />
                    </div>
                    {{-- Church Website --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="website" :value="__('Church Website:')" />
                        <x-text-input id="website" class="border mt-1 w-full text-xl p-2" type="website"
                                      name="website" value="{{$listing->website}}" />
                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                    </div>
                    {{-- Church City --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="city" :value="__('City:')" />
                        <x-text-input id="city" class="border mt-1 w-full text-xl p-2" type="city"
                                      name="city" value="{{$listing->city}}" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    {{-- State --}}
                    <div class="w-5/12 mt-4">
                        <x-input-label for="state" :value="__('State')" />
                        <select name="state" id="state" class="border mt-1 w-full text-xl p-2">
                            <option value="">Select a State:</option>
                            <option value="AL" @selected($listing->state == "AL")>Alabama</option>
                            <option value="AK" @selected($listing->state == "AK")>Alaska</option>
                            <option value="AZ" @selected($listing->state == "AZ")>Arizona</option>
                            <option value="AR" @selected($listing->state == "AR")>Arkansas</option>
                            <option value="CA" @selected($listing->state == "CA")>California</option>
                            <option value="CO" @selected($listing->state == "CO")>Colorado</option>
                            <option value="CT" @selected($listing->state == "CT")>Connecticut</option>
                            <option value="DE" @selected($listing->state == "DE")>Delaware</option>
                            <option value="DC" @selected($listing->state == "DC")>District Of Columbia</option>
                            <option value="FL" @selected($listing->state == "FL")>Florida</option>
                            <option value="GA" @selected($listing->state == "GA")>Georgia</option>
                            <option value="HI" @selected($listing->state == "HI")>Hawaii</option>
                            <option value="ID" @selected($listing->state == "ID")>Idaho</option>
                            <option value="IL" @selected($listing->state == "IL")>Illinois</option>
                            <option value="IN" @selected($listing->state == "IN")>Indiana</option>
                            <option value="IA" @selected($listing->state == "IA")>Iowa</option>
                            <option value="KS" @selected($listing->state == "KS")>Kansas</option>
                            <option value="KY" @selected($listing->state == "KY")>Kentucky</option>
                            <option value="LA" @selected($listing->state == "LA")>Louisiana</option>
                            <option value="ME" @selected($listing->state == "ME")>Maine</option>
                            <option value="MD" @selected($listing->state == "MD")>Maryland</option>
                            <option value="MA" @selected($listing->state == "MA")>Massachusetts</option>
                            <option value="MI" @selected($listing->state == "MI")>Michigan</option>
                            <option value="MN" @selected($listing->state == "MN")>Minnesota</option>
                            <option value="MS" @selected($listing->state == "MS")>Mississippi</option>
                            <option value="MO" @selected($listing->state == "MO")>Missouri</option>
                            <option value="MT" @selected($listing->state == "MT")>Montana</option>
                            <option value="NE" @selected($listing->state == "NE")>Nebraska</option>
                            <option value="NV" @selected($listing->state == "NV")>Nevada</option>
                            <option value="NH" @selected($listing->state == "NH")>New Hampshire</option>
                            <option value="NJ" @selected($listing->state == "NJ")>New Jersey</option>
                            <option value="NM" @selected($listing->state == "NM")>New Mexico</option>
                            <option value="NY" @selected($listing->state == "NY")>New York</option>
                            <option value="NC" @selected($listing->state == "NC")>North Carolina</option>
                            <option value="ND" @selected($listing->state == "ND")>North Dakota</option>
                            <option value="OH" @selected($listing->state == "OH")>Ohio</option>
                            <option value="OK" @selected($listing->state == "OK")>Oklahoma</option>
                            <option value="OR" @selected($listing->state == "OR")>Oregon</option>
                            <option value="PA" @selected($listing->state == "PA")>Pennsylvania</option>
                            <option value="RI" @selected($listing->state == "RI")>Rhode Island</option>
                            <option value="SC" @selected($listing->state == "SC")>South Carolina</option>
                            <option value="SD" @selected($listing->state == "SD")>South Dakota</option>
                            <option value="TN" @selected($listing->state == "TN")>Tennessee</option>
                            <option value="TX" @selected($listing->state == "TX")>Texas</option>
                            <option value="UT" @selected($listing->state == "UT")>Utah</option>
                            <option value="VT" @selected($listing->state == "VT")>Vermont</option>
                            <option value="VA" @selected($listing->state == "VA")>Virginia</option>
                            <option value="WA" @selected($listing->state == "WA")>Washington</option>
                            <option value="WV" @selected($listing->state == "WV")>West Virginia</option>
                            <option value="WI" @selected($listing->state == "WI")>Wisconsin</option>
                            <option value="WY" @selected($listing->state == "WY")>Wyoming</option>
                        </select>
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                    </div>
                    {{-- Listing Body --}}
                    <div class="mt-8 w-full">
                        <label for="listingContent" class="text-lg text-gray-700">More Details: <span class="text-md text-gray-500">This text will be converted to markdown. <a class="underline text-blue-600" target="_blank" href="https://www.markdownguide.org/cheat-sheet/">Learn Markdown</a></span></label>
                        <textarea class="w-full" id="listingContent" rows="10" name="content">{!! $listing->content !!}</textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>
                    {{-- Listing Submit --}}
                    <x-primary-button class="text-white max-w-32 flex justify-center text-center py-2 rounded-lg mt-12" type="submit">Publish</x-primary-button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
