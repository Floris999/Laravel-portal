<?php

use App\Models\Category;
use App\Services\MoneybirdService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Computed;

new #[Title('Categorieën')] class extends Component {
    public bool $isLoading = false;
    public string $message = '';

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    public function updateCategories(): void
    {
        $this->isLoading = true;
        $this->message = '';

        try {
            $service = app(MoneybirdService::class);
            $synced = $service->syncCategories();
            $this->message = __('settings.categories.synced', ['count' => $synced]);
        } catch (\Exception $e) {
            $this->message = __('settings.categories.error', ['error' => $e->getMessage()]);
        } finally {
            $this->isLoading = false;
        }
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('settings.categories.title') }}</flux:heading>

    <x-pages::settings.layout :heading="__('settings.categories.heading')" :subheading="__('settings.categories.subheading')">
        <div class="my-6 w-full space-y-6">
            @if ($this->categories->isEmpty())
                <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                    {{ __('settings.categories.no_categories') }}
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-zinc-200">
                    <table class="w-full text-sm">
                        <thead class="border-b border-zinc-200 bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('settings.categories.name') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('settings.categories.type') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('settings.categories.account_id') }}</th>
                                <th class="px-4 py-3 text-center font-semibold">{{ __('settings.categories.active') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($this->categories as $category)
                                <tr>
                                    <td class="px-4 py-3">{{ $category->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700">
                                            {{ $category->account_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $category->account_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($category->active)
                                            <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                        @else
                                            <span class="inline-flex h-2 w-2 rounded-full bg-zinc-300"></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex gap-2">
                <flux:button
                    variant="primary"
                    wire:click="updateCategories"
                    :loading="$this->isLoading"
                    :disabled="$this->isLoading"
                >
                    {{ __('settings.categories.update_button') }}
                </flux:button>
            </div>

            @if ($this->message)
                <div class="rounded-lg {{ str_contains($this->message, 'Error') ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800' }} p-4 text-sm">
                    {{ $this->message }}
                </div>
            @endif
        </div>
    </x-pages::settings.layout>
</section>
