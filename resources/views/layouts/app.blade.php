<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <!-- Include Jodit CSS Styling -->
    <link rel="stylesheet" href="//unpkg.com/jodit@4.1.16/es2021/jodit.min.css">

    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 pb-10">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <footer>
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500">
                    © {{ date('Y') }} {{ config('app.name') }}.
                    All rights reserved.
                </p>
            </div>
        </div>
{{--        <div class="bg-gray-800 text-white py-4">--}}
{{--            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">--}}
{{--                <p class="text-center">Follow us on:--}}
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">X</a>,--}}
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">Facebook</a>,--}}
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">Instagram</a>--}}
{{--                </p>--}}
{{--            </div>--}}
{{--        </div>--}}
        @if (session()->has('success'))
            <div x-data="{show: true}" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms x-show="show" class="fixed py-4 px-12 bottom-10 right-10 max-w-80 shadow-lg z-10 bg-white rounded-lg">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="mt-0.5 size-6 text-green-500">
                        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm3.844-8.791a.75.75 0 0 0-1.188-.918l-3.7 4.79-1.649-1.833a.75.75 0 1 0-1.114 1.004l2.25 2.5a.75.75 0 0 0 1.15-.043l4.25-5.5Z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                    <span class="sr-only">Success:</span>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{show: true}" x-init="setTimeout(() => show = false, 3000)" x-transition.duration.500ms x-show="show" class="fixed py-4 px-12 bottom-10 right-10 max-w-80 bg-white z-10 rounded-lg shadow-lg">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="mt-0.5 size-6 text-red-600">
                        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('error') }}
                    <span class="sr-only">Error:</span>
                </div>
            </div>
        @endif
    </footer>
{{--    @push('scripts')--}}
        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <script>
            const quill = new Quill('#body_content', { theme: 'snow' });
            quill.on('text-change', function() {
                document.getElementById("bodyContent").value = quill.root.innerHTML;
            });
            const edit_quill = new Quill('#edit_body_content', { theme: 'snow' });
            edit_quill.on('text-change', function() {
                document.getElementById("editBodyContent").value = edit_quill.root.innerHTML;
            });
        </script>
        <!-- Include the Jodit JS Library -->
        <script src="//unpkg.com/jodit@4.1.16/es2021/jodit.min.js"></script>
        @livewireScripts
{{--    @endpush--}}
</body>

</html>
