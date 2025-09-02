<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @turnstileScripts()
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
    <div class="bg-white shadow relative">
        <div
            class="text-center text-white p-6 w-full md:w-1/4 fixed md:bottom-10 bottom-0 md:left-10 bg-blue-800 rounded-lg">
            This site is under construction. Feel free to look around, but expect some dummy content and rough edges.
            <span class="font-bold">We will be officially launching soon!</span>
        </div>
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }}.
                All Rights Reserved.
            </p>
        </div>
    </div>
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
            <div class="w-full text-center text-lg text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                     width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 11.917 9.724 16.5 19 7.5"/>
                </svg>
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
@livewireScripts
</body>
</html>
