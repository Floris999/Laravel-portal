<div class="contents" x-data="{ sidenav: true, closeOnMobile() { if (window.innerWidth < 768) { this.sidenav = false } } }">
    <button @click="sidenav = true"
        class="p-2 border-2 bg-white rounded-md border-gray-200 shadow-lg text-gray-500 focus:bg-teal-500 focus:outline-none focus:text-white absolute top-0 left-0 sm:hidden">
        <svg class="w-5 h-5 fill-current" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                clip-rule="evenodd"></path>
        </svg>
    </button>
    <div id="sidebar"
        class="bg-white h-dvh flex shrink-0 flex-col justify-between border-r border-gray-200 shadow-[4px_0_15px_-3px_rgba(0,0,0,0.1)] px-3 w-30 md:w-60 lg:w-60 overflow-x-hidden transition-transform duration-300 ease-in-out"
        x-show="sidenav" @click.away="closeOnMobile()">
        <div class="space-y-6 md:space-y-10 pt-12">
            <div id="profile" class="space-y-3">
                <div class="flex flex-col items-center gap-2">
                    <x-app-logo-icon class="size-16 fill-current text-gray-800" />
                    <span class="text-lg font-semibold text-gray-800">{{ config('app.name', 'Portal') }}</span>
                </div>
            </div>
            <div id="menu" class="flex flex-col space-y-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="text-sm font-medium text-gray-700 py-2 px-2 hover:bg-orange-500 hover:text-white hover:scale-105 rounded-md transition duration-150 ease-in-out">
                            <span class="">{{ __('sidebar.dashboard') }}</span>
                        </a>
                        <x-sidebar-group :label="__('sidebar.customers')" :active="request()->routeIs('customers.*')">
                            <a href="{{ route('customers.index') }}"
                                class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('customers.*') && !request()->routeIs('customers.archived') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                {{ __('sidebar.customers_overview') }}
                            </a>
                            <a href="{{ route('customers.archived') }}"
                                class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('customers.archived') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                {{ __('sidebar.customers_archived') }}
                            </a>
                        </x-sidebar-group>
                        <x-sidebar-group :label="__('sidebar.suppliers')" :active="request()->routeIs('suppliers.*')">
                            <a href="{{ route('suppliers.index') }}"
                                class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('suppliers.*') && !request()->routeIs('suppliers.archived') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                {{ __('sidebar.suppliers_overview') }}
                            </a>
                            <a href="{{ route('suppliers.archived') }}"
                                class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('suppliers.archived') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                {{ __('sidebar.suppliers_archived') }}
                            </a>
                        </x-sidebar-group>
                        @can('manage-finances')
                            <x-sidebar-group :label="__('sidebar.financial')" :active="request()->routeIs('financial.*')">
                                <a href="{{ route('financial.registration') }}"
                                    class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('financial.registration*') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                    {{ __('sidebar.financial_registration') }}
                                </a>
                                <a href="{{ route('financial.expenses') }}"
                                    class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('financial.expenses*') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                    {{ __('sidebar.financial_expenses') }}
                                </a>
                                <a href="{{ route('financial.uninvoiced') }}"
                                    class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('financial.uninvoiced*') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                    {{ __('sidebar.financial_uninvoiced') }}
                                </a>
                                <a href="{{ route('financial.invoices') }}"
                                    class="text-sm py-2 px-2 hover:bg-orange-500 hover:text-white rounded-md transition duration-150 ease-in-out {{ request()->routeIs('financial.invoices*') ? 'font-semibold text-orange-500' : 'text-gray-700' }}">
                                    {{ __('sidebar.financial_invoices') }}
                                </a>
                            </x-sidebar-group>
                        @endcan
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-700 py-2 px-2 hover:bg-orange-500 hover:text-white hover:scale-105 rounded-md transition duration-150 ease-in-out">
                            <span class="">{{ __('sidebar.login') }}</span>
                        </a>
                    @endauth
                @endif
            </div>
        </div>

        @auth
            <div class="pb-6">
                <div class="pb-3">
                    <h2
                        class="flex items-center justify-center gap-1.5 font-medium text-xs md:text-sm text-center text-orange-500">
                        {{ auth()->user()->name }}
                        <a href="{{ route('profile.edit') }}" wire:navigate
                            aria-label="{{ __('settings.common.settings') }}">
                            <flux:icon.cog-8-tooth class="size-5 cursor-pointer md:size-6" />
                        </a>
                    </h2>
                    <p class="text-center text-xs text-gray-500">
                        {{ auth()->user()->role->value }}
                    </p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-sm font-medium text-gray-700 py-2 px-2 hover:bg-orange-500 hover:text-white hover:scale-105 rounded-md transition duration-150 ease-in-out cursor-pointer">
                        {{ __('sidebar.logout') }}
                    </button>
                </form>
            </div>
        @endauth
    </div>
</div>
