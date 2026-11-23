<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;

class FinancialUninvoicedController extends Controller
{
    /**
     * Display the uninvoiced items page.
     */
    public function index(): View
    {
        return view('financial.uninvoiced');
    }

    /**
     * Display the uninvoiced registrations and expenses of a customer, per month.
     */
    public function show(Customer $customer): View
    {
        return view('financial.uninvoiced-customer', [
            'customer' => $customer,
        ]);
    }
}
