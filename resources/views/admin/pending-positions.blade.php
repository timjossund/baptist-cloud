<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2 class="text-2xl font-bold mb-4">Pending Positions</h2>
            @if ($positions->isEmpty())
                <p>No positions awaiting review.</p>
            @else
                @foreach ($positions as $position)
                    <div class="border-b border-gray-200 py-4 px-4">
                        <h3 class="text-lg font-semibold">{{ $position->position }}</h3>
                        <p>{{ $position->church }}, {{ $position->city }}, {{ $position->state }}</p>
                        <p class="text-sm text-gray-600">
                            Submitted {{ $position->created_at->format('n/j/Y') }} by {{ $position->user?->name ?? 'Unknown' }}
                        </p>
                        <div class="flex items-center gap-4 mt-2">
                            <a href="/position/{{ $position->id }}" class="text-blue-500 hover:underline">View</a>
                            <form action="{{ route('approve-listing', $position) }}" method="post">
                                @csrf
                                <button type="submit" class="text-green-700 font-black py-2">Approve</button>
                            </form>
                            <form action="{{ route('reject-listing', $position) }}" method="post"
                                onsubmit="return confirm('Reject and delete this position? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-black py-2">Reject</button>
                            </form>
                        </div>
                    </div>
                @endforeach
                {{ $positions->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
