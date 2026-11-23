<x-layouts::app :title="__('financial.uninvoiced_customer.title', ['name' => $customer->name])">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $customer->name }}</flux:heading>
                <flux:text>{{ __('financial.uninvoiced_customer.subheading') }}</flux:text>
            </div>

            <flux:button :href="route('financial.uninvoiced')" variant="ghost" wire:navigate>
                {{ __('financial.uninvoiced_customer.back') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <livewire:financial.uninvoiced-customer :customer="$customer" />
    </div>
</x-layouts::app>
