<?php

namespace App\Livewire\Financial;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class UninvoicedTable extends Component
{
    use WithPagination;

    #[Url]
    public string $sort = 'asc';

    #[Url]
    #[Validate('max:255')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    /**
     * Customers that have billable, uninvoiced registrations or expenses.
     *
     * @return Builder<Customer>
     */
    protected function baseQuery(): Builder
    {
        $search = trim($this->search);

        return Customer::query()
            ->where(fn(Builder $query) => $query
                ->whereHas('registrations', fn(Builder $query) => $query->uninvoiced()->where('billable', true))
                ->orWhereHas('expenses', fn(Builder $query) => $query->uninvoiced()->where('billable', true)))
            ->when($search !== '', fn(Builder $query) => $query->where('name', 'like', "%{$search}%"));
    }

    public function render(): View
    {
        $customers = $this->baseQuery()
            ->withSum(['registrations as total_hours' => fn(Builder $query) => $query->uninvoiced()], 'hours')
            ->withSum(['registrations as billable_hours' => fn(Builder $query) => $query->uninvoiced()->where('billable', true)], 'hours')
            ->withSum(['registrations as billable_amount' => fn(Builder $query) => $query->uninvoiced()->where('billable', true)], 'amount_excl_vat')
            ->withSum(['expenses as billable_expenses' => fn(Builder $query) => $query->uninvoiced()->where('billable', true)], 'amount_excl_vat')
            ->orderBy('name', $this->sort === 'desc' ? 'desc' : 'asc')
            ->paginate(10);

        return view('livewire.financial.uninvoiced-table', [
            'customers' => $customers,
        ]);
    }
}
