<x-layouts::app :title="__('suppliers.show.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                @if ($supplier->logo_path)
                    <img src="{{ Storage::disk('public')->url($supplier->logo_path) }}" alt=""
                        class="size-16 rounded-xl border border-neutral-200 object-cover dark:border-neutral-700">
                @endif

                <div>
                    <flux:heading size="xl">{{ $supplier->name }}</flux:heading>
                    <flux:text>{{ __('suppliers.show.heading') }} · #{{ $supplier->supplier_number }}</flux:text>
                    <div class="mt-2">
                        @if ($supplier->active)
                            <flux:badge color="lime" size="sm">{{ __('suppliers.table.active') }}</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">{{ __('suppliers.table.inactive') }}</flux:badge>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button :href="route('suppliers.index')" variant="ghost" wire:navigate>
                    {{ __('suppliers.show.back') }}
                </flux:button>
                @can('update', $supplier)
                    <flux:button :href="route('suppliers.edit', $supplier)" variant="primary" wire:navigate>
                        {{ __('suppliers.show.edit') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8">
                <flux:heading size="lg">{{ __('suppliers.show.company_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.table.id') }}</dt>
                        <dd class="mt-1 font-medium">{{ $supplier->supplier_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.name') }}</dt>
                        <dd class="mt-1 font-medium">{{ $supplier->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.kvk') }}</dt>
                        <dd class="mt-1">{{ $supplier->kvk ?: __('suppliers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.vat_number') }}</dt>
                        <dd class="mt-1">{{ $supplier->vat_number ?: __('suppliers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.supplier_since') }}</dt>
                        <dd class="mt-1">{{ $supplier->supplier_since->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.status') }}</dt>
                        <dd class="mt-1">{{ $supplier->active ? __('suppliers.table.active') : __('suppliers.table.inactive') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8">
                <flux:heading size="lg">{{ __('suppliers.show.contact_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.email') }}</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $supplier->email }}" class="text-accent hover:underline">{{ $supplier->email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.admin_email') }}</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $supplier->administration_email }}" class="text-accent hover:underline">
                                {{ $supplier->administration_email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.phone') }}</dt>
                        <dd class="mt-1">
                            @if ($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="text-accent hover:underline">{{ $supplier->phone }}</a>
                            @else
                                {{ __('suppliers.show.not_provided') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8 lg:col-span-2">
                <flux:heading size="lg">{{ __('suppliers.show.address_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.address') }}</dt>
                        <dd class="mt-1">{{ $supplier->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.postal_code') }}</dt>
                        <dd class="mt-1">{{ $supplier->postal_code ?: __('suppliers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.city') }}</dt>
                        <dd class="mt-1">{{ $supplier->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('suppliers.fields.country') }}</dt>
                        <dd class="mt-1">{{ $supplier->country }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-layouts::app>
