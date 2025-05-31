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
{{--    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />--}}
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
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">X.com</a>,--}}
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">Facebook</a>,--}}
{{--                    <a href="#" class="text-blue-400 hover:text-blue-300">Instagram</a>--}}
{{--                </p>--}}
{{--            </div>--}}
{{--        </div>--}}
        @if (session()->has('success'))
            <div x-data="{show: true}"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 class="fixed py-4 px-12 bottom-10 right-10 max-w-80 shadow-lg z-10 bg-blue-600 rounded-lg">
                <div class="w-full text-center text-lg text-white">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{show: true}"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 class="fixed py-4 px-12 bottom-10 right-10 max-w-80 bg-red-300 z-10 rounded-lg shadow-lg">
                <div class="w-full text-center text-lg">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </footer>
{{--    @push('scripts')--}}
{{--        <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>--}}
{{--        <script>--}}
{{--            const quill = new Quill('#bodycontent', { theme: 'snow' });--}}
{{--            quill.on('text-change', function() {--}}
{{--                document.getElementById("content").value = quill.root.innerHTML;--}}
{{--            });--}}
{{--        </script>--}}
    @livewireScripts
{{--    @endpush--}}
</body>

</html>
