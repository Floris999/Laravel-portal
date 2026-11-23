<x-layouts::app :title="__('financial.invoices.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('financial.invoices.heading') }}</flux:heading>
            <flux:text>{{ __('financial.invoices.subheading') }}</flux:text>
        </div>

        <livewire:financial.invoice-table />
    </div>
</x-layouts::app>
