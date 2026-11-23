<div @if ($hasSending) wire:poll.3s @endif>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div class="w-full max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" :label="__('financial.invoices.search')"
                :placeholder="__('financial.invoices.search_placeholder')" type="search" />
            <flux:error name="search" />
        </div>

        <div class="flex items-center gap-3">
            @if ($selected !== [])
                <flux:text>{{ __('financial.invoices.selected', ['count' => count($selected)]) }}</flux:text>
            @endif

            <flux:dropdown position="bottom" align="end">
                <flux:button icon-trailing="chevron-down" :disabled="$selected === []">
                    {{ __('financial.invoices.actions') }}
                </flux:button>

                <flux:menu>
                    <flux:menu.item as="button" type="button" icon="paper-airplane"
                        wire:click="sendSelectedToMoneybird">
                        {{ __('financial.invoices.send') }}
                    </flux:menu.item>
                    <flux:menu.item as="button" type="button" icon="arrow-uturn-left"
                        wire:click="askConfirmation('restore')">
                        {{ __('financial.invoices.restore') }}
                    </flux:menu.item>
                    <flux:menu.item as="button" type="button" icon="trash" variant="danger"
                        wire:click="askConfirmation('discard')">
                        {{ __('financial.invoices.discard') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-900 dark:text-neutral-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">
                            <flux:checkbox wire:click="togglePage" :checked="$allSelected"
                                aria-label="{{ __('financial.invoices.select_all') }}" />
                        </th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.customer') }}</th>
                        <th scope="col" class="px-4 py-3"
                            aria-sort="{{ $sort === 'desc' ? 'descending' : 'ascending' }}">
                            <button type="button" wire:click="toggleSort"
                                class="inline-flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                {{ __('financial.invoices.table.period') }}
                                <span aria-hidden="true">{{ $sort === 'desc' ? '↓' : '↑' }}</span>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.lines') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.vat_mode') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.total_excl_vat') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.total_incl_vat') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.status') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('financial.invoices.table.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($invoices as $invoice)
                        <tr wire:key="invoice-{{ $invoice->id }}"
                            onclick="window.location='{{ route('financial.invoices.show', $invoice) }}'"
                            class="cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            <td class="px-4 py-3" onclick="event.stopPropagation()">
                                <flux:checkbox wire:model.live="selected" value="{{ $invoice->id }}"
                                    :disabled="! $invoice->status->isActionable()"
                                    aria-label="{{ __('financial.invoices.select_row', ['name' => $invoice->customer->name]) }}" />
                            </td>
                            <td class="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $invoice->customer->name }}</td>
                            <td class="px-4 py-3">{{ $invoice->period->translatedFormat('F Y') }}</td>
                            <td class="px-4 py-3">{{ $invoice->registrations_count + $invoice->expenses_count }}</td>
                            <td class="px-4 py-3">
                                {{ $invoice->price_includes_vat ? __('financial.invoices.incl_vat') : __('financial.invoices.excl_vat') }}
                            </td>
                            <td class="px-4 py-3">€
                                {{ number_format((float) $invoice->total_excl_vat + (float) $invoice->expenses_excl_vat, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">€
                                {{ number_format((float) $invoice->total_incl_vat + (float) $invoice->expenses_incl_vat, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge :color="$invoice->status->color()" size="sm">
                                    {{ $invoice->status->label() }}
                                </flux:badge>
                                @if ($invoice->error !== null && $invoice->status === \App\Enums\InvoiceStatus::Failed)
                                    <flux:text size="sm" class="mt-1 max-w-xs truncate"
                                        title="{{ $invoice->error }}">
                                        {{ $invoice->error }}
                                    </flux:text>
                                @endif
                            </td>
                            <td class="px-4 py-3" onclick="event.stopPropagation()">
                                @if ($invoice->status->isActionable() || $invoice->status->canRetry() || $invoice->status->isDeletable())
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical"
                                            aria-label="{{ __('financial.invoices.table.action') }}" />

                                        <flux:menu>
                                            @if ($invoice->status->canRetry())
                                                <flux:menu.item as="button" type="button" icon="arrow-path"
                                                    wire:click="retryInvoice({{ $invoice->id }})">
                                                    {{ __('financial.invoices.retry') }}
                                                </flux:menu.item>
                                            @elseif ($invoice->status->isActionable())
                                                <flux:menu.item as="button" type="button" icon="paper-airplane"
                                                    wire:click="sendToMoneybird({{ $invoice->id }})">
                                                    {{ __('financial.invoices.send') }}
                                                </flux:menu.item>
                                            @endif
                                            @if ($invoice->status->isActionable())
                                                <flux:menu.item as="button" type="button" icon="arrow-uturn-left"
                                                    wire:click="askConfirmation('restore', {{ $invoice->id }})">
                                                    {{ __('financial.invoices.restore') }}
                                                </flux:menu.item>
                                                <flux:menu.item as="button" type="button" icon="trash"
                                                    variant="danger"
                                                    wire:click="askConfirmation('discard', {{ $invoice->id }})">
                                                    {{ __('financial.invoices.discard') }}
                                                </flux:menu.item>
                                            @endif
                                            @if ($invoice->status->isDeletable())
                                                <flux:menu.item as="button" type="button" icon="trash"
                                                    variant="danger"
                                                    wire:click="askConfirmation('delete', {{ $invoice->id }})">
                                                    {{ __('financial.invoices.delete') }}
                                                </flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">
                                {{ trim($search) === '' ? __('financial.invoices.empty') : __('financial.invoices.no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="border-t border-neutral-200 px-4 py-3 dark:border-neutral-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <flux:modal name="confirm-invoice" wire:model="showConfirm" class="max-w-md">
        @if ($confirmAction !== '')
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">{{ __('financial.invoices.confirm.' . $confirmAction . '.heading') }}
                    </flux:heading>
                    <flux:text>
                        {{ trans_choice('financial.invoices.confirm.' . $confirmAction . '.body', count($confirmIds), ['count' => count($confirmIds)]) }}
                    </flux:text>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" type="button" wire:click="cancelConfirmation">
                        {{ __('financial.invoices.cancel') }}
                    </flux:button>
                    <flux:button :variant="in_array($confirmAction, ['delete', 'discard']) ? 'danger' : 'primary'"
                        type="button" wire:click="executeConfirmation">
                        {{ __('financial.invoices.' . $confirmAction) }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
