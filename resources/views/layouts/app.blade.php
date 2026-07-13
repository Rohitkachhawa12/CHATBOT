<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f7f8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ChatBot')</title>
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    {{-- @vite(['resources/js/app.js']) --}}
    @stack('styles')
</head>
<body>
    <div class="app">
        @include('sidebar.sidebar')

        <div class="overlay" id="overlay"></div>

        @yield('content')
    </div>

    <script src="{{ asset('js/chatbot.js') }}?v={{ filemtime(public_path('js/chatbot.js')) }}"></script>
    @stack('scripts')
</body>
</html>
