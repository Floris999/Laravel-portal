<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('settings.common.settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('settings.profile.heading') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('settings.security.heading') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('settings.appearance.heading') }}</flux:navlist.item>
            @can('manage-users')
                <flux:navlist.item :href="route('users.manage')" wire:navigate>
                    {{ __('users.heading') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('products.manage')" wire:navigate>
                    {{ __('settings.products.heading') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('moneybird.edit')" wire:navigate>
                    {{ __('settings.moneybird.heading') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('categories.manage')" wire:navigate>
                    {{ __('settings.categories.title') }}
                </flux:navlist.item>
            @endcan
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
