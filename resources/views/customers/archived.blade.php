<x-layouts::app :title="__('customers.archived.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('customers.archived.heading') }}</flux:heading>
                <flux:text>{{ __('customers.archived.subheading') }}</flux:text>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-900 dark:text-neutral-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.id') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.name') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.kvk') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.email') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.phone') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('customers.table.city') }}</th>
                            @can('restoreAny', \App\Models\Customer::class)
                                <th scope="col" class="px-4 py-3">{{ __('customers.table.action') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="px-4 py-3">{{ $customer->id }}</td>
                                <td class="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $customer->name }}</td>
                                <td class="px-4 py-3">{{ $customer->kvk }}</td>
                                <td class="px-4 py-3">{{ $customer->email }}</td>
                                <td class="px-4 py-3">{{ $customer->phone }}</td>
                                <td class="px-4 py-3">{{ $customer->city }}</td>
                                @can('restore', $customer)
                                    <td class="px-4 py-3 text-right">
                                        <flux:modal.trigger name="restore-customer-{{ $customer->id }}">
                                            <flux:tooltip :content="__('customers.archived.restore')">
                                                <button type="button" aria-label="{{ __('customers.archived.restore') }}">
                                                    <flux:icon.arrow-uturn-left class="size-5 cursor-pointer md:size-6" />
                                                </button>
                                            </flux:tooltip>
                                        </flux:modal.trigger>

                                        <flux:modal name="restore-customer-{{ $customer->id }}" class="max-w-md text-left">
                                            <form method="POST" action="{{ route('customers.restore', $customer) }}"
                                                class="space-y-6">
                                                @csrf
                                                @method('PATCH')

                                                <div class="space-y-2">
                                                    <flux:heading size="lg">{{ __('customers.archived.restore') }}
                                                    </flux:heading>
                                                    <flux:text>
                                                        {{ __('customers.archived.restore_confirm', ['name' => $customer->name]) }}
                                                    </flux:text>
                                                </div>

                                                <div class="flex justify-end gap-2">
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost" type="button">
                                                            {{ __('customers.cancel') }}</flux:button>
                                                    </flux:modal.close>
                                                    <flux:button type="submit" variant="primary">
                                                        {{ __('customers.archived.restore') }}</flux:button>
                                                </div>
                                            </form>
                                        </flux:modal>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@can('restoreAny', \App\Models\Customer::class) 7 @else 6 @endcan"
                                    class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">
                                    {{ __('customers.archived.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
