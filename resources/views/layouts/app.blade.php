<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'League of Fantasy')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-100 text-base-content">
    <div class="min-h-screen flex flex-col">
        @include('partials.header')

        <main class="max-w-6xl mx-auto w-full px-6 py-8 flex-1">
            <div class="space-y-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>

        @include('partials.footer')
    </div>
</body>
</html>
