<x-layouts::app :title="__('financial.uninvoiced.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('financial.uninvoiced.heading') }}</flux:heading>
            <flux:text>{{ __('financial.uninvoiced.subheading') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <livewire:financial.uninvoiced-table />
    </div>
</x-layouts::app>
