<?php

namespace App\Http\Controllers;

use App\Jobs\SyncCustomerToMoneybird;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $sort = $request->query('sort') === 'asc' ? 'asc' : 'desc';
        $search = trim($validated['search'] ?? '');

        return view('customers.index', [
            'sort' => $sort,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Display the details of a customer.
     */
    public function show(Customer $customer): View
    {
        return view('customers.show', ['customer' => $customer]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('customer-logos', 'public');
        }

        unset($validated['logo']);

        $customer = Customer::create($validated);

        SyncCustomerToMoneybird::dispatch($customer->id)->afterCommit();

        return redirect()->route('customers.index');
    }

    /**
     * Show the form for editing a customer.
     */
    public function edit(Customer $customer): View
    {
        Gate::authorize('update', $customer);

        return view('customers.edit', ['customer' => $customer]);
    }

    /**
     * Update an existing customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $validated = $request->validate($this->rules());

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('customer-logos', 'public');
        }

        unset($validated['logo']);

        $customer->update($validated);

        SyncCustomerToMoneybird::dispatch($customer->id)->afterCommit();

        return redirect()->route('customers.index');
    }

    /**
     * Display a listing of the archived customers.
     */
    public function archived(): View
    {
        return view('customers.archived', [
            'customers' => Customer::onlyTrashed()->latest()->get(),
        ]);
    }

    /**
     * Archive a customer (soft delete, reversible via restore).
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return redirect()->route('customers.index');
    }

    /**
     * Restore a previously archived customer.
     */
    public function restore(Customer $customer): RedirectResponse
    {
        Gate::authorize('restore', $customer);

        $customer->restore();

        return redirect()->route('customers.archived');
    }

    /**
     * Validation rules shared by the create and edit forms.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kvk' => ['nullable', 'string', 'max:20'],
            'vat_number' => ['nullable', 'string', 'max:20'],
            'administration_email' => ['required', 'email', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'customer_since' => ['required', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
