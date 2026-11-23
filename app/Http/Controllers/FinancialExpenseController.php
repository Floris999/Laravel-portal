<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FinancialExpenseController extends Controller
{
    /**
     * Display the expense registration page.
     */
    public function index(): View
    {
        return view('financial.expense', [
            'expense' => null,
            'customers' => Customer::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('active', true)->whereNull('deleted_at')],
            ...$this->rules(),
        ]);

        Expense::create([
            'customer_id' => $validated['customer_id'],
            'user_id' => $request->user()->id,
            'attachment_path' => $request->file('attachment')?->store('expense-attachments'),
            ...$this->fields($request, $validated),
        ]);

        return redirect()->route('financial.expenses')->with('status', __('financial.expense.saved'));
    }

    /**
     * Display the form to change an uninvoiced expense.
     */
    public function edit(Expense $expense): View
    {
        abort_if($expense->invoice_id !== null, 403);

        return view('financial.expense', [
            'expense' => $expense->load('customer'),
        ]);
    }

    /**
     * Update an uninvoiced expense.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->invoice_id !== null, 403);

        $validated = $request->validate($this->rules());

        $attachmentPath = $expense->attachment_path;

        if ($request->hasFile('attachment')) {
            $this->deleteAttachment($expense);
            $attachmentPath = $request->file('attachment')->store('expense-attachments');
        }

        $expense->update([
            'attachment_path' => $attachmentPath,
            ...$this->fields($request, $validated),
        ]);

        return redirect()
            ->route('financial.uninvoiced.show', $expense->customer_id)
            ->with('status', __('financial.expense.updated'));
    }

    /**
     * Delete an uninvoiced expense.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        abort_if($expense->invoice_id !== null, 403);

        $this->deleteAttachment($expense);
        $expense->delete();

        return redirect()
            ->route('financial.uninvoiced.show', $expense->customer_id)
            ->with('status', __('financial.expense.deleted'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'spent_on' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,gif,png,pdf', 'max:10240'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999', 'decimal:0,2'],
            'vat_rate' => ['required', 'integer', Rule::in([21, 9, 0])],
        ];
    }

    /**
     * Columns shared by create and update, including the calculated amounts.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function fields(Request $request, array $validated): array
    {
        $priceIncludesVat = $request->boolean('price_includes_vat');
        $vatFactor = $validated['vat_rate'] / 100;
        $amount = round((float) $validated['amount'], 2);

        if ($priceIncludesVat) {
            $amountInclVat = $amount;
            $amountExclVat = round($amount / (1 + $vatFactor), 2);
            $vatAmount = round($amountInclVat - $amountExclVat, 2);
        } else {
            $amountExclVat = $amount;
            $vatAmount = round($amountExclVat * $vatFactor, 2);
            $amountInclVat = round($amountExclVat + $vatAmount, 2);
        }

        return [
            'spent_on' => $validated['spent_on'],
            'description' => $validated['description'],
            'amount' => $amount,
            'vat_rate' => $validated['vat_rate'],
            'billable' => $request->boolean('billable'),
            'price_includes_vat' => $priceIncludesVat,
            'amount_excl_vat' => $amountExclVat,
            'vat_amount' => $vatAmount,
            'amount_incl_vat' => $amountInclVat,
        ];
    }

    private function deleteAttachment(Expense $expense): void
    {
        if ($expense->attachment_path !== null) {
            Storage::delete($expense->attachment_path);
        }
    }
}
