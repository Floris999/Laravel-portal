<x-layouts::app :title="__('suppliers.edit.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">{{ __('suppliers.edit.heading') }}</flux:heading>
        <flux:text>{{ __('suppliers.edit.subheading', ['name' => $supplier->name]) }}</flux:text>

        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" enctype="multipart/form-data"
            class="flex max-w-2xl flex-col gap-4">
            @csrf
            @method('PUT')

            @include('suppliers._form', ['supplier' => $supplier])

            <div class="flex items-center justify-between gap-2">
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ __('suppliers.edit.submit') }}</flux:button>
                    <flux:button :href="route('suppliers.index')" variant="ghost" wire:navigate>
                        {{ __('suppliers.cancel') }}
                    </flux:button>
                </div>

                @can('delete', $supplier)
                    <flux:modal.trigger name="archive-supplier">
                        <flux:button type="button" variant="danger">{{ __('suppliers.edit.archive') }}</flux:button>
                    </flux:modal.trigger>
                @endcan
            </div>
        </form>

        @can('delete', $supplier)
            <flux:modal name="archive-supplier" class="max-w-md">
                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="space-y-6">
                    @csrf
                    @method('DELETE')

                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('suppliers.edit.archive') }}</flux:heading>
                        <flux:text>{{ __('suppliers.edit.archive_confirm', ['name' => $supplier->name]) }}</flux:text>
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost" type="button">{{ __('suppliers.cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger">{{ __('suppliers.edit.archive') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        @endcan
    </div>
</x-layouts::app>
