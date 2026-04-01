<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Arsenal Away' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen flex flex-col bg-ceefax-bg-1 text-ceefax-white font-mono">
    <header class="bg-ceefax-blue flex items-center justify-between px-4 py-1 text-xs">
        <span class="text-ceefax-yellow">P302</span>
        <a href="/" class="text-ceefax-yellow font-bold tracking-[4px] text-lg no-underline">FOLLOW ARSENAL AWAY</a>
    </header>

    <main class="flex-1 px-4 py-3">
        {{ $slot }}
    </main>

    <footer class="bg-ceefax-blue px-4 py-1 flex justify-between text-xs mt-auto">
        <span class="text-ceefax-yellow tracking-wider">Arsenal Away Fixtures</span>
        <a href="/admin" class="text-ceefax-cyan no-underline">Admin P900</a>
    </footer>

    @fluxScripts
</body>
</html>
