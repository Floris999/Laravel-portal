<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('settings.products.title') }}</flux:heading>

    <x-pages::settings.layout :heading="__('settings.products.heading')" :subheading="__('settings.products.subheading')">
        <div class="my-6 w-full space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('settings.products.list_title') }}</flux:heading>
                <flux:button variant="primary" wire:click="openCreateModal">
                    {{ __('settings.products.add_product') }}
                </flux:button>
            </div>

            @if ($this->products->isEmpty())
                <flux:text>{{ __('settings.products.no_products') }}</flux:text>
            @else
                <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @foreach ($this->products as $product)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex-1">
                                <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $product->name }}
                                </div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                    € {{ number_format($product->hourly_rate, 2, ',', '.') }}
                                </div>
                                @if ($product->category)
                                    <div class="mt-2">
                                        <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $product->category->name }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
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

    public function mount(): void
    {
        $this->authorize('manage-users');
    }

    #[Computed]
    public function products()
    {
        return Product::query()
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

