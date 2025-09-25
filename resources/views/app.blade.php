<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Fontawesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <!-- CSRF token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

         <!-- Web Application Manifest -->
        <link rel="manifest" href="/manifest.json">
        <!-- Chrome for Android theme color -->
        <meta name="theme-color" content="#ffc02a">

        <!-- Add to homescreen for Chrome on Android -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Neverwhere">
        <link rel="icon" sizes="512x512" href="{{ asset('favicon.ico') }}">


        <!-- Add to homescreen for Safari on iOS -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Neverwhere">
        <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">

        <!-- Tile for Win8 -->
        <meta name="msapplication-TileColor" content="#111828">
        <meta name="msapplication-TileImage" content="/images/icons/icon-512x512.png">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
