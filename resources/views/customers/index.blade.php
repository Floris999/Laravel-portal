<x-layouts::app :title="__('customers.index.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('customers.index.heading') }}</flux:heading>
                <flux:text>{{ __('customers.index.subheading') }}</flux:text>
            </div>

            <flux:button :href="route('customers.create')" variant="primary" wire:navigate>
                {{ __('customers.index.add') }}
            </flux:button>
        </div>

        <livewire:customers.customer-table :sort="$sort" :search="$search" />
    </div>
</x-layouts::app>
