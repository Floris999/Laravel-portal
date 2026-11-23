<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <!-- Logo & Branding Section -->
                <div class="flex flex-col items-center gap-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3 font-medium hover:opacity-80 transition-opacity" wire:navigate>
                        <x-app-logo-icon class="size-16 fill-current text-black dark:text-white" />
                        <span class="sr-only">{{ config('app.name', 'Portal') }}</span>
                    </a>
                </div>

                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
