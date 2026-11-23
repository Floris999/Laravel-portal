@props(['label', 'active' => false])

<div x-data="{ open: @js($active) }" class="flex flex-col space-y-1">
    <button type="button" @click="open = !open" :aria-expanded="open"
        class="flex w-full items-center justify-between text-sm font-medium text-gray-700 py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out cursor-pointer">
        <span>{{ $label }}</span>
        <flux:icon.chevron-down class="size-4 transition-transform duration-150" ::class="{ 'rotate-180': open }" />
    </button>

    <div x-show="open" x-cloak x-collapse class="flex flex-col space-y-1 pl-3">
        {{ $slot }}
    </div>
</div>
