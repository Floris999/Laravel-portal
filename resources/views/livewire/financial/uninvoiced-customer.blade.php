<div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <aside
        class="order-first rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900 xl:sticky xl:top-4 xl:order-last">
        <flux:heading size="lg">{{ __('financial.uninvoiced_customer.basket_heading') }}</flux:heading>

        @if ($basketItems->isEmpty())
            <flux:text class="mt-2">{{ __('financial.uninvoiced_customer.basket_empty') }}</flux:text>
        @else
            <ul class="mt-3 divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($basketItems as $key => $item)
                    @php
                        $isRegistration = $item instanceof \App\Models\Registration;
                    @endphp
                    <li wire:key="basket-{{ $key }}" class="flex items-start justify-between gap-3 py-2 text-sm">
                        <div class="min-w-0">
                            <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                {{ $isRegistration ? $item->product->name : __('financial.uninvoiced_customer.expense') }}
                            </div>
                            <div class="break-words text-neutral-500 dark:text-neutral-400">
                                {{ ($isRegistration ? $item->worked_on : $item->spent_on)->format('d-m-Y') }}
                                @if ($item->description)
                                    · {{ $item->description }}
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <span class="whitespace-nowrap">€
                                {{ number_format((float) $item->amount_excl_vat, 2, ',', '.') }}</span>
                            <flux:button size="xs" variant="ghost" icon="x-mark"
                                wire:click="removeFromInvoice('{{ $key }}')"
                                aria-label="{{ __('financial.uninvoiced_customer.remove_from_invoice') }}" />
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-3 flex justify-between gap-4 border-t border-neutral-200 pt-3 dark:border-neutral-700">
                <flux:heading>{{ __('financial.uninvoiced_customer.basket_total') }}</flux:heading>
                <flux:heading class="whitespace-nowrap">€ {{ number_format($basketTotal, 2, ',', '.') }}
                </flux:heading>
            </div>

            @if ($invoiceCount > 1)
                <flux:callout variant="secondary" icon="information-circle" class="mt-3"
                    :heading="__('financial.uninvoiced_customer.multiple_invoices', ['count' => $invoiceCount])" />
            @endif
        @endif

        <flux:button variant="primary" icon="document-plus" class="mt-4 w-full" wire:click="createInvoice"
            wire:loading.attr="disabled" :disabled="$basketItems->isEmpty()">
            {{ __('financial.uninvoiced_customer.create_invoice') }}
        </flux:button>
    </aside>

    <div class="flex min-w-0 flex-col gap-8">
        @forelse ($months as $monthKey => $month)
            <section class="flex flex-col gap-2" wire:key="month-{{ $loop->index }}">
                <flux:heading size="lg">{{ $month['label'] }}</flux:heading>

                <ul
                    class="divide-y divide-neutral-200 overflow-hidden rounded-xl border border-neutral-200 dark:divide-neutral-700 dark:border-neutral-700">
                    @foreach ($month['lines'] as $line)
                        @php
                            $item = $line['item'];
                            $isRegistration = $item instanceof \App\Models\Registration;
                        @endphp
                        <li wire:key="line-{{ $line['key'] }}"
                            class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $isRegistration ? $item->product->name : __('financial.uninvoiced_customer.expense') }}
                                    </span>
                                    @if (!$item->billable)
                                        <flux:badge size="sm" color="zinc">
                                            {{ __('financial.uninvoiced_customer.not_billable') }}</flux:badge>
                                    @elseif ($line['inBasket'])
                                        <flux:badge size="sm" color="lime">
                                            {{ __('financial.uninvoiced_customer.added') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="mt-0.5 break-words text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $line['date']->format('d-m-Y') }}
                                    @if ($item->description)
                                        · {{ $item->description }}
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 md:justify-end">
                                <div class="whitespace-nowrap text-right">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">€
                                        {{ number_format((float) $item->amount_excl_vat, 2, ',', '.') }}</div>
                                    @if ($isRegistration)
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ rtrim(rtrim(number_format((float) $item->hours, 2, ',', '.'), '0'), ',') }}
                                            {{ __('financial.uninvoiced_customer.hours_unit') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($item->billable)
                                        @if ($line['inBasket'])
                                            <flux:button size="sm" variant="ghost" icon="minus"
                                                wire:click="removeFromInvoice('{{ $line['key'] }}')">
                                                {{ __('financial.uninvoiced_customer.remove_from_invoice') }}
                                            </flux:button>
                                        @else
                                            <flux:button size="sm" variant="primary" icon="plus"
                                                wire:click="addToInvoice('{{ $line['key'] }}')">
                                                {{ __('financial.uninvoiced_customer.add_to_invoice') }}
                                            </flux:button>
                                        @endif
                                    @endif

                                    <flux:button size="sm" icon="pencil-square"
                                        :href="$isRegistration ? route('financial.registration.edit', $item) : route(
                                            'financial.expenses.edit', $item)"
                                        aria-label="{{ __('financial.uninvoiced_customer.edit') }}"
                                        title="{{ __('financial.uninvoiced_customer.edit') }}" />

                                    <flux:modal.trigger name="delete-{{ $line['key'] }}">
                                        <flux:button type="button" size="sm" icon="trash" variant="danger"
                                            aria-label="{{ __('financial.uninvoiced_customer.delete') }}"
                                            title="{{ __('financial.uninvoiced_customer.delete') }}" />
                                    </flux:modal.trigger>
                                </div>
                            </div>

                            <flux:modal name="delete-{{ $line['key'] }}" class="max-w-md text-left">
                                <form method="POST"
                                    action="{{ $isRegistration ? route('financial.registration.destroy', $item) : route('financial.expenses.destroy', $item) }}"
                                    class="space-y-6">
                                    @csrf
                                    @method('DELETE')

                                    <div class="space-y-2">
                                        <flux:heading size="lg">
                                            {{ __('financial.uninvoiced_customer.delete_heading') }}</flux:heading>
                                        <flux:text>{{ __('financial.uninvoiced_customer.confirm_delete') }}
                                        </flux:text>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost" type="button">
                                                {{ __('financial.uninvoiced_customer.cancel') }}</flux:button>
                                        </flux:modal.close>
                                        <flux:button type="submit" variant="danger">
                                            {{ __('financial.uninvoiced_customer.delete') }}</flux:button>
                                    </div>
                                </form>
                            </flux:modal>
                        </li>
                    @endforeach
                </ul>

                @if ($month['hasAddable'])
                    <div class="flex justify-end">
                        <flux:button size="sm" icon="plus"
                            wire:click="addMonthToInvoice('{{ $monthKey }}')">
                            {{ __('financial.uninvoiced_customer.add_all_to_invoice') }}
                        </flux:button>
                    </div>
                @endif
            </section>
        @empty
            <flux:text>{{ __('financial.uninvoiced_customer.empty') }}</flux:text>
        @endforelse
    </div>
</div>
