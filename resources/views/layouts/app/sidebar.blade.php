<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="antialiased">
    <div class="flex h-dvh w-full flex-row overflow-hidden">
        <x-app-sidebar />

        <main class="min-h-0 min-w-0 flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>

    @fluxScripts
</body>

</html>
