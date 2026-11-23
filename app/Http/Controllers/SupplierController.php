<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSupplierToMoneybird;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $sort = $request->query('sort') === 'asc' ? 'asc' : 'desc';
        $search = trim($validated['search'] ?? '');

        return view('suppliers.index', [
            'sort' => $sort,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Display the details of a supplier.
     */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', ['supplier' => $supplier]);
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('supplier-logos', 'public');
        }

        unset($validated['logo']);

        $supplier = Supplier::create($validated);

        SyncSupplierToMoneybird::dispatch($supplier->id)->afterCommit();

        return redirect()->route('suppliers.index');
    }

    /**
     * Show the form for editing a supplier.
     */
    public function edit(Supplier $supplier): View
    {
        Gate::authorize('update', $supplier);

        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);

        $validated = $request->validate($this->rules());

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('supplier-logos', 'public');
        }

        unset($validated['logo']);

        $supplier->update($validated);

        SyncSupplierToMoneybird::dispatch($supplier->id)->afterCommit();

        return redirect()->route('suppliers.index');
    }

    /**
     * Display a listing of the archived suppliers.
     */
    public function archived(): View
    {
        return view('suppliers.archived', [
            'suppliers' => Supplier::onlyTrashed()->latest()->get(),
        ]);
    }

    /**
     * Archive a supplier (soft delete, reversible via restore).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        Gate::authorize('delete', $supplier);

        $supplier->delete();

        return redirect()->route('suppliers.index');
    }

    /**
     * Restore a previously archived supplier.
     */
    public function restore(Supplier $supplier): RedirectResponse
    {
        Gate::authorize('restore', $supplier);

        $supplier->restore();

        return redirect()->route('suppliers.archived');
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
            'supplier_since' => ['required', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
