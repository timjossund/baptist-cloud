<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col items-start gap-6">
            <h2 class="text-4xl font-bold">Choose a plan</h2>
            <p class="text-gray-700 max-w-2xl">Free accounts get 5 articles and 2 sermons a month. Subscribe for unlimited access. Cancel anytime.</p>
            <x-input-error :messages="$errors->get('plan')" />
            <x-plan-cards />
        </div>
    </div>
</x-app-layout>
