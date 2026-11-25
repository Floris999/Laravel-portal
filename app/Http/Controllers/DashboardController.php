<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with the main totals.
     */
    public function index(): View
    {
        return view('dashboard', [
            'customerCount' => Customer::count(),
            'supplierCount' => Supplier::count(),
            'uninvoicedCount' => Registration::uninvoiced()->where('billable', true)->count() + Expense::uninvoiced()->where('billable', true)->count(),
            'syncedInvoiceCount' => Invoice::where('status', InvoiceStatus::Synced->value)->count(),
            'customers' => Customer::where('active', true)->whereNotNull('latitude')->whereNotNull('longitude')->get(['id', 'name', 'latitude', 'longitude', 'address', 'phone', 'email']),
        ]);
    }
}
