<?php

namespace App\Livewire\Financial;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Registration;
use Carbon\CarbonInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class UninvoicedCustomer extends Component
{
    public Customer $customer;

    /** @var array<int, string> */
    public array $basket = [];

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function addToInvoice(string $key): void
    {
        $item = $this->items()->get($key);

        if ($item === null || ! $item->billable || in_array($key, $this->basket, true)) {
            return;
        }

        $this->basket[] = $key;
    }

    public function removeFromInvoice(string $key): void
    {
        $this->basket = array_values(array_diff($this->basket, [$key]));
    }

    /**
     * Add all billable registrations and expenses of a month (format Y-m) to the invoice.
     */
    public function addMonthToInvoice(string $month): void
    {
        $keys = $this->items()
            ->filter(fn(Registration|Expense $item) => $item->billable && $this->dateOf($item)->format('Y-m') === $month)
            ->keys()
            ->all();

        $this->basket = array_values(array_unique([...$this->basket, ...$keys]));
    }

    public function createInvoice(): void
    {
        Gate::authorize('manage-finances');

        $basketItems = $this->basketItems();

        if ($basketItems->isEmpty()) {
            return;
        }

        $invoiceCount = DB::transaction(function () use ($basketItems): int {
            $registrations = Registration::query()
                ->uninvoiced()
                ->where('billable', true)
                ->where('customer_id', $this->customer->id)
                ->whereIn('id', $basketItems->whereInstanceOf(Registration::class)->pluck('id'))
                ->lockForUpdate()
                ->get();

            $expenses = Expense::query()
                ->uninvoiced()
                ->where('billable', true)
                ->where('customer_id', $this->customer->id)
                ->whereIn('id', $basketItems->whereInstanceOf(Expense::class)->pluck('id'))
                ->lockForUpdate()
                ->get();

            $groups = $registrations->toBase()->concat($expenses)->groupBy(fn(Registration|Expense $line) => $this->invoiceGroupOf($line));

            foreach ($groups as $group) {
                $first = $group->first();

                $invoice = Invoice::create([
                    'customer_id' => $this->customer->id,
                    'user_id' => Auth::id(),
                    'period' => $this->dateOf($first)->copy()->startOfMonth(),
                    'price_includes_vat' => $first->price_includes_vat,
                    'status' => InvoiceStatus::Concept,
                ]);

                Registration::query()->whereIn('id', $group->whereInstanceOf(Registration::class)->pluck('id'))->update(['invoice_id' => $invoice->id]);
                Expense::query()->whereIn('id', $group->whereInstanceOf(Expense::class)->pluck('id'))->update(['invoice_id' => $invoice->id]);
            }

            return $groups->count();
        });

        $this->basket = [];

        Flux::toast(variant: 'success', text: trans_choice('financial.uninvoiced_customer.invoices_created', $invoiceCount, ['count' => $invoiceCount]));
    }

    /**
     * Uninvoiced registrations and expenses of the customer, keyed by "r-{id}" or "e-{id}".
     *
     * @return Collection<string, Registration|Expense>
     */
    protected function items(): Collection
    {
        $registrations = $this->customer->registrations()->uninvoiced()->with('product')->get();
        $expenses = $this->customer->expenses()->uninvoiced()->get();

        return $registrations->toBase()->concat($expenses)
            ->sortBy(fn(Registration|Expense $item) => [$this->dateOf($item)->timestamp, $item instanceof Registration ? 0 : 1, $item->id])
            ->keyBy(fn(Registration|Expense $item) => ($item instanceof Registration ? 'r-' : 'e-') . $item->id);
    }

    /**
     * Billable items that are currently added to the invoice.
     *
     * @return Collection<string, Registration|Expense>
     */
    protected function basketItems(): Collection
    {
        return $this->items()->only($this->basket)->filter(fn(Registration|Expense $item) => $item->billable);
    }

    protected function dateOf(Registration|Expense $line): CarbonInterface
    {
        return $line instanceof Registration ? $line->worked_on : $line->spent_on;
    }

    /**
     * One invoice per calendar month and VAT mode, because Moneybird sets VAT mode per invoice.
     */
    protected function invoiceGroupOf(Registration|Expense $line): string
    {
        return $this->dateOf($line)->format('Y-m') . '|' . (int) $line->price_includes_vat;
    }

    public function render(): View
    {
        $items = $this->items();
        $basketItems = $this->basketItems();

        $months = $items
            ->map(fn(Registration|Expense $item, string $key) => [
                'key' => $key,
                'item' => $item,
                'date' => $this->dateOf($item),
                'inBasket' => $basketItems->has($key),
            ])
            ->values()
            ->groupBy(fn(array $line) => $line['date']->format('Y-m'))
            ->sortKeys()
            ->map(fn(Collection $lines, string $month) => [
                'label' => Carbon::createFromFormat('!Y-m', $month)->translatedFormat('F Y'),
                'lines' => $lines,
                'hasAddable' => $lines->contains(fn(array $line) => $line['item']->billable && ! $line['inBasket']),
            ]);

        return view('livewire.financial.uninvoiced-customer', [
            'months' => $months,
            'basketItems' => $basketItems,
            'basketTotal' => $basketItems->sum(fn(Registration|Expense $item) => (float) $item->amount_excl_vat),
            'invoiceCount' => $basketItems->groupBy(fn(Registration|Expense $item) => $this->invoiceGroupOf($item))->count(),
        ]);
    }
}
