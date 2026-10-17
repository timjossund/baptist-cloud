<x-app-layout>
    <div class="max-w-7xl mx-auto mt-10 px-5">
        <div class="bg-white py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col items-start gap-4">
            <h2 class="text-4xl font-bold">Author subscription</h2>
            <p class="text-2xl">$20/month</p>
            <p class="text-gray-700 max-w-2xl">Subscribe to place your own ad at the top of your articles. Cancel anytime.</p>
            <form action="{{ route('billing.subscribe') }}" method="POST">
                @csrf
                <x-primary-button type="submit">Subscribe</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
