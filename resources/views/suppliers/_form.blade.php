@php
    $supplier ??= null;
@endphp

<flux:input name="name" :label="__('suppliers.fields.name')" :value="old('name', $supplier->name ?? '')"
    placeholder="{{ __('suppliers.fields.name_placeholder') }}" required autofocus />

<flux:input name="kvk" :label="__('suppliers.fields.kvk')" :value="old('kvk', $supplier->kvk ?? '')"
    placeholder="{{ __('suppliers.fields.kvk_placeholder') }}" maxlength="20" />

<flux:input name="vat_number" :label="__('suppliers.fields.vat_number')"
    :value="old('vat_number', $supplier->vat_number ?? '')"
    placeholder="{{ __('suppliers.fields.vat_number_placeholder') }}" maxlength="20" />

<flux:input type="email" name="administration_email" :label="__('suppliers.fields.admin_email')"
    :value="old('administration_email', $supplier->administration_email ?? '')"
    placeholder="{{ __('suppliers.fields.admin_email_placeholder') }}" required />

<flux:input type="email" name="email" :label="__('suppliers.fields.email')"
    :value="old('email', $supplier->email ?? '')" placeholder="{{ __('suppliers.fields.email_placeholder') }}"
    required />

<flux:input name="phone" :label="__('suppliers.fields.phone')" :value="old('phone', $supplier->phone ?? '')"
    placeholder="{{ __('suppliers.fields.phone_placeholder') }}" />

<flux:input type="date" name="supplier_since" :label="__('suppliers.fields.supplier_since')"
    :value="old('supplier_since', optional($supplier)->supplier_since?->format('Y-m-d') ?? now()->format('Y-m-d'))"
    required />

<flux:input name="address" :label="__('suppliers.fields.address')" :value="old('address', $supplier->address ?? '')"
    placeholder="{{ __('suppliers.fields.address_placeholder') }}" required />

<flux:input name="postal_code" :label="__('suppliers.fields.postal_code')"
    :value="old('postal_code', $supplier->postal_code ?? '')"
    placeholder="{{ __('suppliers.fields.postal_code_placeholder') }}" />

<flux:input name="city" :label="__('suppliers.fields.city')" :value="old('city', $supplier->city ?? '')"
    placeholder="{{ __('suppliers.fields.city_placeholder') }}" required />

<flux:select name="country" :label="__('suppliers.fields.country')" required>
    @foreach (__('suppliers.countries') as $country)
        <flux:select.option :value="$country"
            :selected="old('country', $supplier->country ?? 'Nederland') === $country">
            {{ $country }}
        </flux:select.option>
    @endforeach
</flux:select>

<flux:select name="active" :label="__('suppliers.fields.status')" required>
    <flux:select.option value="1" :selected="old('active', $supplier->active ?? true) == true">
        {{ __('suppliers.table.active') }}
    </flux:select.option>
    <flux:select.option value="0" :selected="old('active', $supplier->active ?? true) == false">
        {{ __('suppliers.table.inactive') }}</flux:select.option>
</flux:select>

<flux:input type="file" name="logo" :label="__('suppliers.fields.logo')" accept=".jpg,.jpeg,.png" />
