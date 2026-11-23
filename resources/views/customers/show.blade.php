<x-layouts::app :title="__('customers.show.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                @if ($customer->logo_path)
                    <img src="{{ Storage::disk('public')->url($customer->logo_path) }}" alt=""
                        class="size-16 rounded-xl border border-neutral-200 object-cover dark:border-neutral-700">
                @endif

                <div>
                    <flux:heading size="xl">{{ $customer->name }}</flux:heading>
                    <flux:text>{{ __('customers.show.heading') }} · #{{ $customer->id }}</flux:text>
                    <div class="mt-2">
                        @if ($customer->active)
                            <flux:badge color="lime" size="sm">{{ __('customers.table.active') }}</flux:badge>
                        @else
                            <flux:badge color="red" size="sm">{{ __('customers.table.inactive') }}</flux:badge>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button :href="route('customers.index')" variant="ghost" wire:navigate>
                    {{ __('customers.show.back') }}
                </flux:button>
                @can('update', $customer)
                    <flux:button :href="route('customers.edit', $customer)" variant="primary" wire:navigate>
                        {{ __('customers.show.edit') }}
                    </flux:button>
                @endcan
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8">
                <flux:heading size="lg">{{ __('customers.show.company_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.table.id') }}</dt>
                        <dd class="mt-1 font-medium">{{ $customer->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.name') }}</dt>
                        <dd class="mt-1 font-medium">{{ $customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.kvk') }}</dt>
                        <dd class="mt-1">{{ $customer->kvk ?: __('customers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.vat_number') }}</dt>
                        <dd class="mt-1">{{ $customer->vat_number ?: __('customers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.customer_since') }}</dt>
                        <dd class="mt-1">{{ $customer->customer_since->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.status') }}</dt>
                        <dd class="mt-1">{{ $customer->active ? __('customers.table.active') : __('customers.table.inactive') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8">
                <flux:heading size="lg">{{ __('customers.show.contact_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.email') }}</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $customer->email }}" class="text-accent hover:underline">{{ $customer->email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.admin_email') }}</dt>
                        <dd class="mt-1">
                            <a href="mailto:{{ $customer->administration_email }}" class="text-accent hover:underline">
                                {{ $customer->administration_email }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.phone') }}</dt>
                        <dd class="mt-1">
                            @if ($customer->phone)
                                <a href="tel:{{ $customer->phone }}" class="text-accent hover:underline">{{ $customer->phone }}</a>
                            @else
                                {{ __('customers.show.not_provided') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8 lg:col-span-2">
                <flux:heading size="lg">{{ __('customers.show.address_details') }}</flux:heading>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.address') }}</dt>
                        <dd class="mt-1">{{ $customer->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.postal_code') }}</dt>
                        <dd class="mt-1">{{ $customer->postal_code ?: __('customers.show.not_provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.city') }}</dt>
                        <dd class="mt-1">{{ $customer->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('customers.fields.country') }}</dt>
                        <dd class="mt-1">{{ $customer->country }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-layouts::app>
