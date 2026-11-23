<x-layouts::auth :title="__('auth.register_title')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('auth.register_heading')" :description="__('auth.register_description')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('auth.name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('auth.name_placeholder')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('auth.email')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                :placeholder="__('auth.email_placeholder')"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('auth.password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('auth.password_placeholder')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('auth.password_confirm')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('auth.password_confirm_placeholder')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('auth.register_button') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('auth.have_account') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('auth.login_button') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
