{{--
    The application shell.

    This is the one view a module is allowed to depend on by name. It is the
    presentation equivalent of ApiController: a shared frame that says nothing
    about any particular module's domain.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#17181c">
    <title>@yield('title', 'Modularity')</title>

    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-neutral-50 text-neutral-900 antialiased dark:bg-neutral-950 dark:text-neutral-100">
<div class="min-h-full">
    @auth
        <x-navigation/>
    @endauth

    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6">
        @if (session('status'))
            <x-flash tone="success">{{ session('status') }}</x-flash>
        @endif

        @if (session('error'))
            <x-flash tone="danger">{{ session('error') }}</x-flash>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
