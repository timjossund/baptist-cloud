@props(['kind', 'resetsOn'])

@php
    $noun = $kind === 'sermon' ? 'sermons' : 'articles';
@endphp

<div class="mt-8 rounded-xl border border-blue-200 bg-blue-50 p-6 flex flex-col items-start gap-3">
    <h2 class="text-2xl font-bold text-gray-900">You've used your free {{ $noun }} this month</h2>
    <p class="text-gray-700">
        Become a Member for $15/month to read every article and listen to every sermon, on the website and in the mobile app.
    </p>
    <p class="text-gray-500">Your free {{ $noun }} reset on {{ $resetsOn->format('F j') }}.</p>
    <a href="{{ route('billing') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        See the Member plan
    </a>
</div>
