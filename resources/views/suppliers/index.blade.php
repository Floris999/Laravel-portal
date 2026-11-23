<x-layouts::app :title="__('suppliers.index.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('suppliers.index.heading') }}</flux:heading>
                <flux:text>{{ __('suppliers.index.subheading') }}</flux:text>
            </div>

            <flux:button :href="route('suppliers.create')" variant="primary" wire:navigate>
                {{ __('suppliers.index.add') }}
            </flux:button>
        </div>

        <livewire:suppliers.supplier-table :sort="$sort" :search="$search" />
    </div>
</x-layouts::app>
