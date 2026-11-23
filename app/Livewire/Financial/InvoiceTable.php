<?php

namespace App\Livewire\Financial;

use App\Enums\InvoiceStatus;
use App\Jobs\SendInvoiceToMoneybird;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Registration;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceTable extends Component
{
    use WithPagination;

    #[Url]
    public string $sort = 'desc';

    #[Url]
    #[Validate('max:255')]
    public string $search = '';

    /** @var array<int, string> */
    public array $selected = [];

    public bool $showConfirm = false;

    #[Locked]
    public string $confirmAction = '';

    /** @var array<int, string> */
    #[Locked]
    public array $confirmIds = [];

    public function updatedSearch(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function togglePage(): void
    {
        $pageIds = $this->pageIds();

        $this->selected = $this->isPageSelected($pageIds)
            ? array_values(array_diff($this->selected, $pageIds))
            : array_values(array_unique([...$this->selected, ...$pageIds]));
    }

    public function sendToMoneybird(int $invoiceId): void
    {
        $this->send([(string) $invoiceId]);
    }

    public function sendSelectedToMoneybird(): void
    {
        $this->send($this->selected);
    }

    public function retryInvoice(int $invoiceId): void
    {
        $invoice = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Sending->value, InvoiceStatus::Failed->value])
            ->find($invoiceId);

        if ($invoice === null) {
            $this->rejectSelection();

            return;
        }

        $invoice->update(['status' => InvoiceStatus::Sending, 'error' => null]);

        SendInvoiceToMoneybird::dispatch($invoice->id);

        Flux::toast(variant: 'success', text: __('financial.invoices.retry_queued'));
    }

    /**
     * Open the confirmation modal for the invoice, or for the selection when no invoice is given.
     */
    public function askConfirmation(string $action, ?int $invoiceId = null): void
    {
        $ids = $invoiceId === null ? $this->selected : [(string) $invoiceId];

        $query = match ($action) {
            'restore' => $this->actionableQuery($ids),
            'discard' => $this->actionableQuery($ids),
            'delete' => $this->syncedQuery($ids),
            default => null,
        };

        $eligible = $query?->pluck('id') ?? collect();

        if ($eligible->isEmpty()) {
            $this->rejectSelection();

            return;
        }

        $this->confirmAction = $action;
        $this->confirmIds = $eligible->map(fn(int $id) => (string) $id)->all();
        $this->showConfirm = true;
    }

    public function cancelConfirmation(): void
    {
        $this->showConfirm = false;
        $this->confirmAction = '';
        $this->confirmIds = [];
    }

    public function executeConfirmation(): void
    {
        match ($this->confirmAction) {
            'restore' => $this->restore($this->confirmIds),
            'discard' => $this->discard($this->confirmIds),
            'delete' => $this->deleteSynced($this->confirmIds),
            default => null,
        };

        $this->cancelConfirmation();
    }

    /**
     * @param  array<int, string>  $invoiceIds
     */
    protected function send(array $invoiceIds): void
    {
        $ids = $this->actionableQuery($invoiceIds)->pluck('id');

        if ($ids->isEmpty()) {
            $this->rejectSelection();

            return;
        }

        // Sending status keeps a second click from dispatching the same invoice twice.
        Invoice::query()->whereIn('id', $ids)->update(['status' => InvoiceStatus::Sending->value, 'error' => null]);

        foreach ($ids as $id) {
            SendInvoiceToMoneybird::dispatch($id);
        }

        $this->selected = array_values(array_diff($this->selected, $ids->map(fn(int $id) => (string) $id)->all()));

        Flux::toast(variant: 'success', text: trans_choice('financial.invoices.send_queued', $ids->count(), ['count' => $ids->count()]));
    }

    /**
     * Put concept invoices back; their registrations return to the uninvoiced overview.
     *
     * @param  array<int, string>  $invoiceIds
     */
    protected function restore(array $invoiceIds): void
    {
        $ids = $this->actionableQuery($invoiceIds)->pluck('id');

        if ($ids->isEmpty()) {
            $this->rejectSelection();

            return;
        }

        DB::transaction(function () use ($ids): void {
            Registration::query()->whereIn('invoice_id', $ids)->update(['invoice_id' => null]);
            Expense::query()->whereIn('invoice_id', $ids)->update(['invoice_id' => null]);
            Invoice::query()->whereIn('id', $ids)->forceDelete();
        });

        $this->selected = array_values(array_diff($this->selected, $ids->map(fn(int $id) => (string) $id)->all()));

        Flux::toast(variant: 'success', text: trans_choice('financial.invoices.restored', $ids->count(), ['count' => $ids->count()]));
    }

    /**
     * Delete concept invoices together with their registrations and expenses.
     *
     * @param  array<int, string>  $invoiceIds
     */
    protected function discard(array $invoiceIds): void
    {
        $ids = $this->actionableQuery($invoiceIds)->pluck('id');

        if ($ids->isEmpty()) {
            $this->rejectSelection();

            return;
        }

        DB::transaction(function () use ($ids): void {
            Registration::query()->whereIn('invoice_id', $ids)->delete();
            Expense::query()->whereIn('invoice_id', $ids)->delete();
            Invoice::query()->whereIn('id', $ids)->forceDelete();
        });

        $this->selected = array_values(array_diff($this->selected, $ids->map(fn(int $id) => (string) $id)->all()));

        Flux::toast(variant: 'success', text: trans_choice('financial.invoices.discarded', $ids->count(), ['count' => $ids->count()]));
    }

    /**
     * Delete synced invoices from the overview; their registrations stay invoiced and Moneybird is not touched.
     *
     * @param  array<int, string>  $invoiceIds
     */
    protected function deleteSynced(array $invoiceIds): void
    {
        $ids = $this->syncedQuery($invoiceIds)->pluck('id');

        if ($ids->isEmpty()) {
            $this->rejectSelection();

            return;
        }

        Invoice::query()->whereIn('id', $ids)->delete();

        $this->selected = array_values(array_diff($this->selected, $ids->map(fn(int $id) => (string) $id)->all()));

        Flux::toast(variant: 'success', text: trans_choice('financial.invoices.deleted', $ids->count(), ['count' => $ids->count()]));
    }

    /**
     * Invoices that can still be sent or put back (concept or failed).
     *
     * @param  array<int, string>  $invoiceIds
     * @return Builder<Invoice>
     */
    protected function actionableQuery(array $invoiceIds): Builder
    {
        return Invoice::query()
            ->whereIn('id', $invoiceIds)
            ->whereIn('status', [InvoiceStatus::Concept->value, InvoiceStatus::Failed->value]);
    }

    /**
     * @param  array<int, string>  $invoiceIds
     * @return Builder<Invoice>
     */
    protected function syncedQuery(array $invoiceIds): Builder
    {
        return Invoice::query()
            ->whereIn('id', $invoiceIds)
            ->where('status', InvoiceStatus::Synced->value);
    }

    protected function rejectSelection(): void
    {
        $this->selected = [];

        Flux::toast(variant: 'warning', text: __('financial.invoices.nothing_to_do'));
    }

    /**
     * @param  iterable<Invoice>  $invoices
     * @return array<int, string>
     */
    protected function actionableIds(iterable $invoices): array
    {
        return collect($invoices)
            ->filter(fn(Invoice $invoice) => $invoice->status->isActionable())
            ->map(fn(Invoice $invoice) => (string) $invoice->id)
            ->values()
            ->all();
    }

    /**
     * @return Builder<Invoice>
     */
    protected function baseQuery(): Builder
    {
        $search = trim($this->search);

        return Invoice::query()
            ->when($search !== '', fn(Builder $query) => $query->whereHas(
                'customer',
                fn(Builder $query) => $query->where('name', 'like', "%{$search}%"),
            ));
    }

    /**
     * @return array<int, string>
     */
    protected function pageIds(): array
    {
        return $this->actionableIds(
            $this->baseQuery()
                ->orderBy('period', $this->sort === 'asc' ? 'asc' : 'desc')
                ->orderBy('id', $this->sort === 'asc' ? 'asc' : 'desc')
                ->paginate(10)
                ->getCollection(),
        );
    }

    /**
     * @param  array<int, string>  $pageIds
     */
    protected function isPageSelected(array $pageIds): bool
    {
        return $pageIds !== [] && array_diff($pageIds, $this->selected) === [];
    }

    public function render(): View
    {
        $direction = $this->sort === 'asc' ? 'asc' : 'desc';

        $invoices = $this->baseQuery()
            ->with('customer')
            ->withCount(['registrations', 'expenses'])
            ->withSum('registrations as total_excl_vat', 'amount_excl_vat')
            ->withSum('registrations as total_incl_vat', 'amount_incl_vat')
            ->withSum('expenses as expenses_excl_vat', 'amount_excl_vat')
            ->withSum('expenses as expenses_incl_vat', 'amount_incl_vat')
            ->orderBy('period', $direction)
            ->orderBy('id', $direction)
            ->paginate(10);

        return view('livewire.financial.invoice-table', [
            'invoices' => $invoices,
            'hasSending' => Invoice::query()->where('status', InvoiceStatus::Sending->value)->exists(),
            'allSelected' => $this->isPageSelected($this->actionableIds($invoices->getCollection())),
        ]);
    }
}
