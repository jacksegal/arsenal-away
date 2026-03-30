<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Arsenal Away' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:brand href="/" name="Arsenal Away" class="font-bold" />

        <flux:spacer />

        <flux:navbar>
            <flux:navbar.item href="/" icon="table-cells" current>Fixtures</flux:navbar.item>
            <flux:navbar.item href="/admin" icon="cog-6-tooth">Admin</flux:navbar.item>
        </flux:navbar>
    </flux:header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @fluxScripts
</body>
</html>
