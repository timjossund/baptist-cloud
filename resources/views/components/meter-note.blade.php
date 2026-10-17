@props(['kind', 'meter'])

@if ($meter && $meter->allowed && $meter->remaining !== null && $meter->remaining <= 1)
    @php
        $noun = $kind === 'sermon' ? 'sermon' : 'article';
    @endphp
    <p class="mb-4 rounded-md bg-amber-50 px-4 py-2 text-sm text-amber-900" role="status">
        @if ($meter->remaining === 1)
            1 free {{ $noun }} left this month
        @else
            That was your last free {{ $noun }} this month
        @endif
    </p>
@endif
