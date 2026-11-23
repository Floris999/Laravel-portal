<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierTable extends Component
{
    use WithPagination;

    #[Url]
    public string $sort = 'desc';

    #[Url]
    #[Validate('max:255')]
    public string $search = '';

    public function mount(string $sort = 'desc', string $search = ''): void
    {
        $this->sort = $sort === 'asc' ? 'asc' : 'desc';
        $this->search = $search;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $sort = $this->sort === 'asc' ? 'asc' : 'desc';

        $suppliers = Supplier::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('administration_email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->orderBy('id', $sort)
            ->paginate(10);

        return view('livewire.suppliers.supplier-table', [
            'suppliers' => $suppliers,
        ]);
    }
}
