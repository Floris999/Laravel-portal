<div>
    <div class="mb-4 w-full max-w-md">
        <flux:input wire:model.live.debounce.300ms="search" :label="__('financial.uninvoiced.search')"
            :placeholder="__('financial.uninvoiced.search_placeholder')" type="search" />
        <flux:error name="search" />
    </div>

    <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-900 dark:text-neutral-400">
                    <tr>
                        <th scope="col" class="px-4 py-3"
                            aria-sort="{{ $sort === 'desc' ? 'descending' : 'ascending' }}">
                            <button type="button" wire:click="toggleSort"
                                class="inline-flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                {{ __('financial.uninvoiced.table.customer') }}
                                <span aria-hidden="true">{{ $sort === 'desc' ? '↓' : '↑' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.uninvoiced.table.total_hours') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.uninvoiced.table.billable_hours') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.uninvoiced.table.expenses') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.uninvoiced.table.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}"
                            onclick="window.location='{{ route('financial.uninvoiced.show', $customer) }}'"
                            class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                                <a href="{{ route('financial.uninvoiced.show', $customer) }}"
                                    class="hover:text-orange-500">{{ $customer->name }}</a>
                            </td>
                            <td class="px-4 py-3">{{ number_format((float) $customer->total_hours, 1, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $customer->billable_hours, 1, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">€
                                {{ number_format((float) $customer->billable_expenses, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">€
                                {{ number_format((float) $customer->billable_amount + (float) $customer->billable_expenses, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">
                                {{ trim($search) === '' ? __('financial.uninvoiced.empty') : __('financial.uninvoiced.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
