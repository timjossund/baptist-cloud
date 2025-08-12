<x-app-layout>
    <div class="max-w-7xl w-full mx-auto mt-10 px-5">
        <div class="bg-white sm:py-12 mx-auto px-6 lg:px-8 rounded-lg shadow-sm sm:rounded-lg flex flex-col">
            <h2>Reported Posts</h2>
            @foreach ($reports as $report)
                <div class="border-b border-gray-200 py-4 px-4">
                    <h3>Post Reported: {{ $report->post_title }}</h3>
                    Reported by {{ $report->reporter_name }}
                    <p class="text-lg">Reason: {{ $report->description }}</p>
                    <a href="/<?= '@' . $report->username . '/' . $report->post_slug ?>" class="text-blue-500 hover:underline">View Post</a>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
