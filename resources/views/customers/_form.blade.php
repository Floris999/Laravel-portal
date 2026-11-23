@php
    $customer ??= null;
@endphp

<flux:input name="name" :label="__('customers.fields.name')" :value="old('name', $customer->name ?? '')"
    placeholder="{{ __('customers.fields.name_placeholder') }}" required autofocus />

<flux:input name="kvk" :label="__('customers.fields.kvk')" :value="old('kvk', $customer->kvk ?? '')"
    placeholder="{{ __('customers.fields.kvk_placeholder') }}" maxlength="20" />

<flux:input name="vat_number" :label="__('customers.fields.vat_number')"
    :value="old('vat_number', $customer->vat_number ?? '')"
    placeholder="{{ __('customers.fields.vat_number_placeholder') }}" maxlength="20" />

<flux:input type="email" name="administration_email" :label="__('customers.fields.admin_email')"
    :value="old('administration_email', $customer->administration_email ?? '')"
    placeholder="{{ __('customers.fields.admin_email_placeholder') }}" required />

<flux:input type="email" name="email" :label="__('customers.fields.email')"
    :value="old('email', $customer->email ?? '')" placeholder="{{ __('customers.fields.email_placeholder') }}"
    required />

<flux:input name="phone" :label="__('customers.fields.phone')" :value="old('phone', $customer->phone ?? '')"
    placeholder="{{ __('customers.fields.phone_placeholder') }}" />

<flux:input type="date" name="customer_since" :label="__('customers.fields.customer_since')"
    :value="old('customer_since', optional($customer)->customer_since?->format('Y-m-d') ?? now()->format('Y-m-d'))"
    required />

<flux:input name="address" :label="__('customers.fields.address')" :value="old('address', $customer->address ?? '')"
    placeholder="{{ __('customers.fields.address_placeholder') }}" required />

<flux:input name="postal_code" :label="__('customers.fields.postal_code')"
    :value="old('postal_code', $customer->postal_code ?? '')"
    placeholder="{{ __('customers.fields.postal_code_placeholder') }}" />

<flux:input name="city" :label="__('customers.fields.city')" :value="old('city', $customer->city ?? '')"
    placeholder="{{ __('customers.fields.city_placeholder') }}" required />

<flux:select name="country" :label="__('customers.fields.country')" required>
    @foreach (__('customers.countries') as $country)
        <flux:select.option :value="$country"
            :selected="old('country', $customer->country ?? 'Nederland') === $country">
            {{ $country }}
        </flux:select.option>
    @endforeach
</flux:select>

<flux:select name="active" :label="__('customers.fields.status')" required>
    <flux:select.option value="1" :selected="old('active', $customer->active ?? true) == true">
        {{ __('customers.table.active') }}
    </flux:select.option>
    <flux:select.option value="0" :selected="old('active', $customer->active ?? true) == false">
        {{ __('customers.table.inactive') }}</flux:select.option>
</flux:select>

<flux:input type="file" name="logo" :label="__('customers.fields.logo')" accept=".jpg,.jpeg,.png" />
