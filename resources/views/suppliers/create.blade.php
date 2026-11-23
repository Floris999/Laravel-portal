<x-layouts::app :title="__('suppliers.create.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">{{ __('suppliers.create.heading') }}</flux:heading>
        <flux:text>{{ __('suppliers.create.subheading') }}</flux:text>

        <form method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data"
            class="flex max-w-2xl flex-col gap-4">
            @csrf

            @include('suppliers._form')

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('suppliers.create.submit') }}</flux:button>
                <flux:button :href="route('suppliers.index')" variant="ghost" wire:navigate>{{ __('suppliers.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
