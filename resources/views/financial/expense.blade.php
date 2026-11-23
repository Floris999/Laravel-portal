<x-layouts::app :title="$expense === null ? __('financial.expense.title') : __('financial.expense.edit_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">
            {{ $expense === null ? __('financial.expense.heading') : __('financial.expense.edit_heading') }}
        </flux:heading>
        <flux:text>
            {{ $expense === null ? __('financial.expense.subheading') : __('financial.expense.edit_subheading') }}
        </flux:text>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" class="max-w-2xl" />
        @endif

        <form method="POST"
            action="{{ $expense === null ? route('financial.expenses.store') : route('financial.expenses.update', $expense) }}"
            enctype="multipart/form-data" class="flex max-w-2xl flex-col gap-4" x-data="expenseForm(@js([
                'amount' => old('amount', $expense?->amount ?? ''),
                'vatRate' => (int) old('vat_rate', $expense?->vat_rate ?? 21),
                'includesVat' => (bool) old('price_includes_vat', $expense?->price_includes_vat ?? false),
            ]))">
            @csrf
            @if ($expense !== null)
                @method('PUT')
            @endif

            @if ($expense === null)
                <div x-data="customerCombobox(@js([
                    'customers' => $customers->map(fn($customer) => ['id' => (string) $customer->id, 'name' => $customer->name])->values(),
                    'selectedId' => (string) old('customer_id', ''),
                ]))" x-on:click.outside="close()" class="relative">
                    <input type="hidden" name="customer_id" :value="selectedId">

                    <flux:input x-model="query" :label="__('financial.expense.customer')"
                        :placeholder="__('financial.expense.customer_placeholder')" icon="magnifying-glass"
                        autocomplete="off" required x-on:focus="open = true; $event.target.select()"
                        x-on:input="open = true; highlighted = 0; selectedId = ''" x-on:keydown.escape="close()"
                        x-on:keydown.arrow-down.prevent="open = true; highlighted = Math.min(highlighted + 1, filtered.length - 1)"
                        x-on:keydown.arrow-up.prevent="highlighted = Math.max(highlighted - 1, 0)"
                        x-on:keydown.enter="if (open) { $event.preventDefault(); choseHighlighted() }" />

                    <ul x-show="open" x-cloak x-transition.opacity
                        class="absolute z-20 mt-1 max-h-72 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        <template x-for="(customer, index) in filtered" :key="customer.id">
                            <li x-on:click="select(customer)" x-on:mouseenter="highlighted = index"
                                x-text="customer.name"
                                :class="{ 'bg-zinc-100 dark:bg-zinc-700': highlighted === index }"
                                class="cursor-pointer px-3 py-2 text-sm text-zinc-800 dark:text-white"></li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-3 py-2 text-sm text-zinc-500">
                            {{ __('financial.expense.customer_no_results') }}
                        </li>
                    </ul>

                    <flux:error name="customer_id" />
                </div>
            @else
                <flux:input :label="__('financial.expense.customer')" :value="$expense->customer->name" readonly
                    disabled />
            @endif

            <flux:input type="date" name="spent_on" :label="__('financial.expense.spent_on')"
                :value="old('spent_on', $expense?->spent_on->format('Y-m-d') ?? now()->format('Y-m-d'))" required />

            <flux:input type="text" name="description" :label="__('financial.expense.description')"
                :placeholder="__('financial.expense.description_placeholder')"
                :value="old('description', $expense?->description)" maxlength="255" required />

            <flux:input type="file" name="attachment" :label="__('financial.expense.attachment')"
                accept=".jpg,.jpeg,.gif,.png,.pdf" />
            <flux:text size="sm">{{ __('financial.expense.attachment_help') }}</flux:text>
            @if ($expense?->attachment_path)
                <flux:text size="sm">{{ __('financial.expense.attachment_current') }}</flux:text>
            @endif

            <flux:input type="number" name="amount" :label="__('financial.expense.amount')" x-model="amount"
                min="0.01" step="0.01" required />

            <flux:select name="vat_rate" :label="__('financial.expense.vat_rate')" x-model.number="vatRate" required>
                @foreach ([21, 9, 0] as $rate)
                    <flux:select.option :value="$rate">{{ $rate }}%</flux:select.option>
                @endforeach
            </flux:select>

            <flux:checkbox name="billable" value="1" :checked="old('billable', $expense?->billable ?? true)"
                :label="__('financial.expense.billable')" />

            <flux:checkbox name="price_includes_vat" value="1" x-model="includesVat"
                :label="__('financial.expense.price_includes_vat')" />

            <div class="flex flex-col gap-1">
                <div class="flex justify-between">
                    <flux:text>{{ __('financial.expense.exclusive_vat') }}</flux:text>
                    <flux:text x-text="euro(excl)"></flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('financial.expense.vat_amount') }} (<span x-text="vatRate"></span>%)
                    </flux:text>
                    <flux:text x-text="euro(vat)"></flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:heading>{{ __('financial.expense.total_inclusive_vat') }}</flux:heading>
                    <flux:heading x-text="euro(incl)"></flux:heading>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $expense === null ? __('financial.expense.submit') : __('financial.expense.save') }}
                </flux:button>
                <flux:button
                    :href="$expense === null ? route('dashboard') : route('financial.uninvoiced.show', $expense->customer_id)"
                    variant="ghost" wire:navigate>
                    {{ __('financial.expense.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
