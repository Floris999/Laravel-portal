<?php

use App\Services\MoneybirdService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Moneybird-instellingen')] class extends Component {
    public bool $connected = false;
    public ?string $message = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->checkConnection();
    }

    /**
     * Check the Moneybird connection and store the result.
     */
    public function checkConnection(): void
    {
        $result = app(MoneybirdService::class)->checkConnection();

        $this->connected = $result['connected'];
        $this->message = $result['message'];
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('settings.moneybird.title') }}</flux:heading>

    <x-pages::settings.layout :heading="__('settings.moneybird.heading')" :subheading="__('settings.moneybird.subheading')">
        <div class="my-6 w-full space-y-6">
            <flux:callout :variant="$connected ? 'success' : 'danger'" :icon="$connected ? 'check-circle' : 'x-circle'" data-test="moneybird-status">
                <flux:callout.heading>
                    {{ $connected ? __('settings.moneybird.connected') : __('settings.moneybird.not_connected') }}
                </flux:callout.heading>
                <flux:callout.text>
                    {{ $connected ? __('settings.moneybird.connected_description') : $message }}
                </flux:callout.text>
            </flux:callout>

            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('settings.moneybird.administration_id') }}</dt>
                    <dd class="font-medium">{{ config('services.moneybird.administration_id') ?: __('settings.moneybird.api_token_missing') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('settings.moneybird.api_token') }}</dt>
                    <dd class="font-medium">{{ config('services.moneybird.api_token') ? __('settings.moneybird.api_token_set') : __('settings.moneybird.api_token_missing') }}</dd>
                </div>
            </dl>

            <flux:text size="sm">{{ __('settings.moneybird.config_hint') }}</flux:text>

            <flux:button wire:click="checkConnection" icon="arrow-path" data-test="moneybird-check-button">
                {{ __('settings.moneybird.check_again') }}
            </flux:button>
        </div>
    </x-pages::settings.layout>
</section>
