<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class FinancialInvoiceController extends Controller
{
    /**
     * Display the concept invoices page.
     */
    public function index(): View
    {
        return view('financial.invoices');
    }

    /**
     * Display a preview of what the invoice will look like in Moneybird.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'customer',
            'registrations' => fn($query) => $query->orderBy('worked_on')->orderBy('id'),
            'registrations.product',
            'expenses' => fn($query) => $query->orderBy('spent_on')->orderBy('id'),
        ]);

        $lines = $invoice->registrations->concat($invoice->expenses);

        $vatSummary = $lines
            ->groupBy('vat_rate')
            ->map(fn($lines, $rate) => [
                'rate' => (int) $rate,
                'excl' => $lines->sum(fn($line) => (float) $line->amount_excl_vat),
                'vat' => $lines->sum(fn($line) => (float) $line->vat_amount),
            ])
            ->sortByDesc('rate')
            ->values();

        return view('financial.invoice', [
            'invoice' => $invoice,
            'vatSummary' => $vatSummary,
            'totalExcl' => $vatSummary->sum('excl'),
            'totalVat' => $vatSummary->sum('vat'),
            'totalIncl' => $lines->sum(fn($line) => (float) $line->amount_incl_vat),
        ]);
    }
}
