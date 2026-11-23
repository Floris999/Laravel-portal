<x-layouts::app :title="__('customers.create.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">{{ __('customers.create.heading') }}</flux:heading>
        <flux:text>{{ __('customers.create.subheading') }}</flux:text>

        <form method="POST" action="{{ route('customers.store') }}" enctype="multipart/form-data"
            class="flex max-w-2xl flex-col gap-4">
            @csrf

            @include('customers._form')

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ __('customers.create.submit') }}</flux:button>
                <flux:button :href="route('customers.index')" variant="ghost" wire:navigate>{{ __('customers.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
