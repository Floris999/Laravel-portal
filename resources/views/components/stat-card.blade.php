@props(['label', 'value', 'icon', 'href' => null])

@php
    $classes = 'group rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900';
@endphp

<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" wire:navigate @endif
    {{ $attributes->class([$classes, 'transition hover:bg-neutral-50 dark:hover:bg-neutral-800' => $href]) }}>
    <div class="flex items-center justify-between">
        <flux:text>{{ $label }}</flux:text>
        <span
            class="flex size-10 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400">
            <flux:icon :name="$icon" class="size-5" />
        </span>
    </div>
    <div class="mt-4 text-4xl font-semibold text-neutral-900 dark:text-neutral-100">
        {{ number_format($value, 0, ',', '.') }}
    </div>
    @if ($href)
        <flux:text size="sm" class="mt-2 group-hover:underline">{{ __('dashboard.view') }}</flux:text>
    @endif
    </{{ $href ? 'a' : 'div' }}>
