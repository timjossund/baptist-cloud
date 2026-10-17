@php
    $plans = [
        [
            'key' => 'member',
            'name' => 'Member',
            'price' => '$15/month',
            'summary' => 'Unlimited articles and sermons, plus the mobile app.',
        ],
        [
            'key' => 'author',
            'name' => 'Author',
            'price' => '$25/month',
            'summary' => 'Everything in Member, plus your own ad on your articles.',
        ],
    ];
@endphp

<div class="grid w-full gap-6 md:grid-cols-2">
    @foreach ($plans as $plan)
        <div class="flex flex-col items-start gap-4 rounded-xl border border-gray-200 p-6">
            <h3 class="text-3xl font-bold">{{ $plan['name'] }}</h3>
            <p class="text-2xl">{{ $plan['price'] }}</p>
            <p class="text-gray-700">{{ $plan['summary'] }}</p>
            <form action="{{ route('billing.subscribe') }}" method="POST" class="mt-auto">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan['key'] }}">
                <x-primary-button type="submit" aria-label="Subscribe to {{ $plan['name'] }}">Subscribe</x-primary-button>
            </form>
        </div>
    @endforeach
</div>
