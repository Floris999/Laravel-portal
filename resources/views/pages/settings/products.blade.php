<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('settings.products.title') }}</flux:heading>

    <x-pages::settings.layout :heading="__('settings.products.heading')" :subheading="__('settings.products.subheading')" wide>
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ __('settings.products.list_title') }}</flux:heading>
                    <flux:text size="sm">{{ __('settings.products.count', ['count' => $this->products->count()]) }}</flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateModal">
                    {{ __('settings.products.add_product') }}
                </flux:button>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        icon="magnifying-glass"
                        :placeholder="__('settings.products.search')"
                        clearable
                    />
                </div>
                <div class="sm:w-56">
                    <flux:select wire:model.live="categoryFilter">
                        <option value="">{{ __('settings.products.all_categories') }}</option>
                        <option value="none">{{ __('settings.products.uncategorized') }}</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            @if ($this->products->isEmpty())
                <flux:text>
                    {{ $this->hasActiveFilters ? __('settings.products.no_results') : __('settings.products.no_products') }}
                </flux:text>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($this->products as $product)
                        <div wire:key="product-{{ $product->id }}" class="flex flex-col justify-between gap-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $product->name }}
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            € {{ number_format($product->hourly_rate, 2, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('settings.products.per_hour') }}</div>
                                    </div>
                                </div>
                                @if ($product->category)
                                    <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2 border-t border-neutral-200 pt-3 dark:border-neutral-700">
                                <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $product->id }})">
                                    {{ __('settings.products.edit') }}
                                </flux:button>
                                <flux:button size="sm" variant="ghost" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" wire:click="openDeleteModal({{ $product->id }})">
                                    {{ __('settings.products.delete') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </x-pages::settings.layout>

    <!-- Create Modal -->
    <flux:modal name="create-product-modal" wire:model="showCreateModal" class="max-w-sm">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('settings.products.create_title') }}</flux:heading>
                <flux:text>{{ __('settings.products.create_description') }}</flux:text>
            </div>

            <form wire:submit="createProduct" class="space-y-6">
                <flux:input
                    wire:model="name"
                    :label="__('settings.products.name')"
                    type="text"
                    required
                    autofocus
                />

                <flux:input
                    wire:model="hourly_rate"
                    :label="__('settings.products.hourly_rate')"
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                />

                <flux:select
                    wire:model="category_id"
                    :label="__('settings.categories.label')"
                    placeholder="{{ __('Selecteer een categorie (optioneel)') }}"
                >
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('settings.common.cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" type="submit">{{ __('settings.products.create_button') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal name="edit-product-modal" wire:model="showEditModal" class="max-w-sm">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('settings.products.edit_title') }}</flux:heading>
                <flux:text>{{ __('settings.products.edit_description') }}</flux:text>
            </div>

            <form wire:submit="updateProduct" class="space-y-6">
                <flux:input
                    wire:model="name"
                    :label="__('settings.products.name')"
                    type="text"
                    required
                    autofocus
                />

                <flux:input
                    wire:model="hourly_rate"
                    :label="__('settings.products.hourly_rate')"
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                />

                <flux:select
                    wire:model="category_id"
                    :label="__('settings.categories.label')"
                    placeholder="{{ __('Selecteer een categorie (optioneel)') }}"
                >
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('settings.common.cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" type="submit">{{ __('settings.common.save') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete Modal -->
    <flux:modal name="delete-product-modal" wire:model="showDeleteModal" class="max-w-sm">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('settings.products.delete_title') }}</flux:heading>
                <flux:text>{{ __('settings.products.delete_confirmation') }}</flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('settings.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteProduct">
                    {{ __('settings.products.delete_button') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>

<?php

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Producten')] class extends Component {

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingProductId = null;
    public ?int $deletingProductId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|numeric|min:0.01')]
    public string $hourly_rate = '';

    #[Validate('nullable|exists:categories,id')]
    public ?int $category_id = null;

    public string $search = '';

    public string $categoryFilter = '';

    public function mount(): void
    {
        $this->authorize('manage-users');
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return trim($this->search) !== '' || $this->categoryFilter !== '';
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when(trim($this->search) !== '', fn ($query) => $query->where('name', 'like', '%'.trim($this->search).'%'))
            ->when($this->categoryFilter === 'none', fn ($query) => $query->whereNull('category_id'))
            ->when(ctype_digit($this->categoryFilter), fn ($query) => $query->where('category_id', (int) $this->categoryFilter))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal(Product $product): void
    {
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->hourly_rate = (string) $product->hourly_rate;
        $this->category_id = $product->category_id;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function openDeleteModal(Product $product): void
    {
        $this->deletingProductId = $product->id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingProductId = null;
    }

    public function createProduct(): void
    {
        $validated = $this->validate();

        Product::create($validated);

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateProduct(): void
    {
        $validated = $this->validate();

        Product::findOrFail($this->editingProductId)->update($validated);

        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteProduct(): void
    {
        Product::findOrFail($this->deletingProductId)->delete();

        $this->showDeleteModal = false;
        $this->deletingProductId = null;
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->hourly_rate = '';
        $this->category_id = null;
        $this->editingProductId = null;
        $this->resetErrorBag();
    }
}; ?>

