<x-layouts::app :title="$registration === null ? __('financial.registration.title') : __('financial.registration.edit_title')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:heading size="xl">
            {{ $registration === null ? __('financial.registration.heading') : __('financial.registration.edit_heading') }}
        </flux:heading>
        <flux:text>
            {{ $registration === null ? __('financial.registration.subheading') : __('financial.registration.edit_subheading') }}
        </flux:text>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" class="max-w-2xl" />
        @endif

        <form method="POST"
            action="{{ $registration === null ? route('financial.registration.store') : route('financial.registration.update', $registration) }}"
            enctype="multipart/form-data" class="flex max-w-2xl flex-col gap-4" x-data="registrationForm(@js([
                'rates' => $products->mapWithKeys(fn($product) => [$product->id => (float) $product->hourly_rate])->when($registration, fn($rates) => $rates->put($registration->product_id, (float) $registration->hourly_rate)),
                'productId' => (string) old('product_id', $registration?->product_id ?? ''),
                'hours' => old('hours', $registration?->hours ?? ''),
                'vatRate' => (int) old('vat_rate', $registration?->vat_rate ?? 21),
                'includesVat' => (bool) old('price_includes_vat', $registration?->price_includes_vat ?? false),
            ]))">
            @csrf
            @if ($registration !== null)
                @method('PUT')
            @endif

            @if ($registration === null)
                <div x-data="customerCombobox(@js([
                    'customers' => $customers->map(fn($customer) => ['id' => (string) $customer->id, 'name' => $customer->name])->values(),
                    'selectedId' => (string) old('customer_id', ''),
                ]))" x-on:click.outside="close()" class="relative">
                    <input type="hidden" name="customer_id" :value="selectedId">

                    <flux:input x-model="query" :label="__('financial.registration.customer')"
                        :placeholder="__('financial.registration.customer_placeholder')" icon="magnifying-glass"
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
                            {{ __('financial.registration.customer_no_results') }}
                        </li>
                    </ul>

                    <flux:error name="customer_id" />
                </div>
            @else
                <flux:input :label="__('financial.registration.customer')" :value="$registration->customer->name"
                    readonly disabled />
            @endif

            <flux:select name="product_id" :label="__('financial.registration.product')" x-model="productId" required>
                <flux:select.option value="">{{ __('financial.registration.product_placeholder') }}
                </flux:select.option>
                @foreach ($products as $product)
                    <flux:select.option :value="$product->id">
                        {{ $product->name }} - € {{ number_format((float) $product->hourly_rate, 2, ',', '.') }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:input type="date" name="worked_on" :label="__('financial.registration.worked_on')"
                :value="old('worked_on', $registration?->worked_on->format('Y-m-d') ?? now()->format('Y-m-d'))"
                required />

            <flux:textarea name="description" :label="__('financial.registration.description')"
                :placeholder="__('financial.registration.description_placeholder')" rows="3">
                {{ old('description', $registration?->description) }}</flux:textarea>

            <flux:input type="file" name="attachment" :label="__('financial.registration.attachment')"
                accept=".jpg,.jpeg,.gif,.png,.pdf" />
            <flux:text size="sm">{{ __('financial.registration.attachment_help') }}</flux:text>
            @if ($registration?->attachment_path)
                <flux:text size="sm">{{ __('financial.registration.attachment_current') }}</flux:text>
            @endif

            <flux:input type="number" name="hours" :label="__('financial.registration.hours')" x-model="hours"
                min="0.01" step="0.01" required />
            <flux:text size="sm">{{ __('financial.registration.hours_help') }}</flux:text>

            <flux:select name="vat_rate" :label="__('financial.registration.vat_rate')" x-model.number="vatRate"
                required>
                @foreach ([21, 9, 0] as $rate)
                    <flux:select.option :value="$rate">{{ $rate }}%</flux:select.option>
                @endforeach
            </flux:select>

            <flux:checkbox name="billable" value="1" :checked="old('billable', $registration?->billable ?? true)"
                :label="__('financial.registration.billable')" />

            <flux:checkbox name="price_includes_vat" value="1" x-model="includesVat"
                :label="__('financial.registration.price_includes_vat')" />

            <div class="flex flex-col gap-1">
                <div class="flex justify-between">
                    <flux:text>{{ __('financial.registration.hourly_rate') }}</flux:text>
                    <flux:text x-text="euro(rate)"></flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('financial.registration.exclusive_vat') }}</flux:text>
                    <flux:text x-text="euro(excl)"></flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('financial.registration.vat_amount') }} (<span x-text="vatRate"></span>%)
                    </flux:text>
                    <flux:text x-text="euro(vat)"></flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:heading>{{ __('financial.registration.total_inclusive_vat') }}</flux:heading>
                    <flux:heading x-text="euro(incl)"></flux:heading>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">
                    {{ $registration === null ? __('financial.registration.submit') : __('financial.registration.save') }}
                </flux:button>
                <flux:button
                    :href="$registration === null ? route('dashboard') : route('financial.uninvoiced.show', $registration->customer_id)"
                    variant="ghost" wire:navigate>
                    {{ __('financial.registration.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
