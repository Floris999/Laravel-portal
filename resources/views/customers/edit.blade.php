<x-layouts::app :title="__('customers.edit.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">{{ __('customers.edit.heading') }}</flux:heading>
        <flux:text>{{ __('customers.edit.subheading', ['name' => $customer->name]) }}</flux:text>

        <form method="POST" action="{{ route('customers.update', $customer) }}" enctype="multipart/form-data"
            class="flex max-w-2xl flex-col gap-4">
            @csrf
            @method('PUT')

            @include('customers._form', ['customer' => $customer])

            <div class="flex items-center justify-between gap-2">
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ __('customers.edit.submit') }}</flux:button>
                    <flux:button :href="route('customers.index')" variant="ghost" wire:navigate>
                        {{ __('customers.cancel') }}
                    </flux:button>
                </div>

                @can('delete', $customer)
                    <flux:modal.trigger name="archive-customer">
                        <flux:button type="button" variant="danger">{{ __('customers.edit.archive') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </form>

        @can('delete', $customer)
            <flux:modal name="archive-customer" class="max-w-md">
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('customers.edit.archive') }}</flux:heading>
                        <flux:text>{{ __('customers.edit.archive_confirm', ['name' => $customer->name]) }}</flux:text>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost" type="button">{{ __('customers.cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger">{{ __('customers.edit.archive') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endcan
    </div>
</x-layouts::app>
