<x-layouts::app :title="__('financial.invoices.preview.title')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('financial.invoices.preview.heading') }}</flux:heading>
                <flux:text>{{ $invoice->customer->name }} · {{ $invoice->period->translatedFormat('F Y') }}</flux:text>
                <div class="mt-2">
                    <flux:badge :color="$invoice->status->color()" size="sm">{{ $invoice->status->label() }}
                    </flux:badge>
                </div>
            </div>

            <flux:button :href="route('financial.invoices')" variant="ghost" wire:navigate>
                {{ __('financial.invoices.preview.back') }}
            </flux:button>
        </div>

        <flux:callout variant="secondary" icon="information-circle" :heading="__('financial.invoices.preview.notice')" />

        <section
            class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900 sm:p-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-sm text-neutral-500 dark:text-neutral-400">
                        {{ __('financial.invoices.preview.customer') }}</dt>
                    <dd class="mt-1 font-medium">{{ $invoice->customer->name }}</dd>
                    <dd class="text-sm">{{ $invoice->customer->address }}</dd>
                    <dd class="text-sm">{{ trim($invoice->customer->postal_code . ' ' . $invoice->customer->city) }}
                    </dd>
                    <dd class="text-sm">{{ $invoice->customer->country }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-neutral-500 dark:text-neutral-400">
                        {{ __('financial.invoices.preview.reference') }}</dt>
                    <dd class="mt-1">
                        {{ __('financial.invoices.moneybird_reference', ['period' => $invoice->period->translatedFormat('F Y')]) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-neutral-500 dark:text-neutral-400">
                        {{ __('financial.invoices.preview.invoice_date') }}</dt>
                    <dd class="mt-1">{{ __('financial.invoices.preview.invoice_date_value') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-neutral-500 dark:text-neutral-400">
                        {{ __('financial.invoices.preview.prices') }}</dt>
                    <dd class="mt-1">
                        {{ $invoice->price_includes_vat ? __('financial.invoices.incl_vat') : __('financial.invoices.excl_vat') }}
                    </dd>
                </div>
            </dl>
        </section>

        <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-900 dark:text-neutral-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">{{ __('financial.invoices.preview.description') }}
                            </th>
                            <th scope="col" class="px-4 py-3">{{ __('financial.invoices.preview.quantity') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('financial.invoices.preview.price') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('financial.invoices.preview.vat') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('financial.invoices.preview.total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @foreach ($invoice->registrations as $registration)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $registration->product->name }} -
                                        {{ $registration->worked_on->format('d-m-Y') }}
                                    </span>
                                    @if ($registration->description)
                                        <span> - {{ $registration->description }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ rtrim(rtrim(number_format((float) $registration->hours, 2, ',', '.'), '0'), ',') }}
                                </td>
                                <td class="px-4 py-3">€
                                    {{ number_format((float) $registration->hourly_rate, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $registration->vat_rate }}%</td>
                                <td class="px-4 py-3">€
                                    {{ number_format((float) $registration->amount_excl_vat, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @foreach ($invoice->expenses as $expense)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $expense->description }} - {{ $expense->spent_on->format('d-m-Y') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">1</td>
                                <td class="px-4 py-3">€ {{ number_format((float) $expense->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3">{{ $expense->vat_rate }}%</td>
                                <td class="px-4 py-3">€
                                    {{ number_format((float) $expense->amount_excl_vat, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-1 border-t border-neutral-200 px-4 py-4 dark:border-neutral-700 sm:items-end">
                <div class="flex w-full justify-between gap-8 sm:w-80">
                    <flux:text>{{ __('financial.invoices.preview.subtotal') }}</flux:text>
                    <flux:text>€ {{ number_format($totalExcl, 2, ',', '.') }}</flux:text>
                </div>
                @foreach ($vatSummary as $summary)
                    <div class="flex w-full justify-between gap-8 sm:w-80">
                        <flux:text>{{ __('financial.invoices.preview.vat_amount', ['rate' => $summary['rate']]) }}
                        </flux:text>
                        <flux:text>€ {{ number_format($summary['vat'], 2, ',', '.') }}</flux:text>
                    </div>
                @endforeach
                <div class="flex w-full justify-between gap-8 sm:w-80">
                    <flux:heading>{{ __('financial.invoices.preview.grand_total') }}</flux:heading>
                    <flux:heading>€ {{ number_format($totalIncl, 2, ',', '.') }}</flux:heading>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
