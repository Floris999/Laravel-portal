<div>
    <div class="mb-4 max-w-md">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :label="__('suppliers.index.search')"
            :placeholder="__('suppliers.index.search_placeholder')"
            type="search"
        />
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
                                {{ __('suppliers.table.id') }}
                                <span aria-hidden="true">{{ $sort === 'desc' ? '↓' : '↑' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.name') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.kvk') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.email') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.phone') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.city') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.supplier_since') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.status') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('suppliers.table.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($suppliers as $supplier)
                        <tr onclick="window.location='{{ route('suppliers.show', $supplier) }}'"
                            class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-3">{{ $supplier->supplier_number }}</td>
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $supplier->name }}</td>
                            <td class="px-4 py-3">{{ $supplier->kvk }}</td>
                            <td class="px-4 py-3">{{ $supplier->email }}</td>
                            <td class="px-4 py-3">{{ $supplier->phone }}</td>
                            <td class="px-4 py-3">{{ $supplier->city }}</td>
                            <td class="px-4 py-3">{{ $supplier->supplier_since->format('d-m-Y') }}</td>
                            <td class="px-4 py-3">
                                @if ($supplier->active)
                                    <flux:badge color="lime" size="sm">{{ __('suppliers.table.active') }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="red" size="sm">{{ __('suppliers.table.inactive') }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3" x-data="{
                                copyStatus: '',
                                async copySupplier(value) {
                                    try {
                                        await navigator.clipboard.writeText(value);
                                        this.copyStatus = 'copied';
                                    } catch (error) {
                                        console.error('Could not copy supplier details.', error);
                                        this.copyStatus = 'error';
                                    }
                                }
                            }" x-on:click.stop>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical"
                                        aria-label="{{ __('suppliers.table.action') }}" />

                                    <flux:menu>
                                        <flux:menu.item as="button" type="button" icon="document-duplicate"
                                            x-on:click.stop="copySupplier(@js($supplier->supplier_number . ' ' . $supplier->name))">
                                            {{ __('suppliers.table.copy') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                                <span x-show="copyStatus === 'error'" x-cloak class="text-xs text-red-600 dark:text-red-400"
                                    role="alert">
                                    {{ __('suppliers.table.copy_error') }}
                                </span>
                                <span class="sr-only" role="status" aria-live="polite"
                                    x-text="copyStatus === 'copied' ? @js(__('suppliers.table.copy_success')) : (copyStatus === 'error' ? @js(__('suppliers.table.copy_error')) : '')"></span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">
                                {{ trim($search) === '' ? __('suppliers.index.empty') : __('suppliers.index.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>
