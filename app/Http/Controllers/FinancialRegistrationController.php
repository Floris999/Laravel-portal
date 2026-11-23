<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FinancialRegistrationController extends Controller
{
    /**
     * Display the financial registration page.
     */
    public function index(): View
    {
        return view('financial.registration', [
            'registration' => null,
            'customers' => Customer::query()->where('active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'hourly_rate']),
        ]);
    }

    /**
     * Store a new registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('active', true)->whereNull('deleted_at')],
            ...$this->rules(),
        ]);

        $hourlyRate = (float) Product::findOrFail($validated['product_id'])->hourly_rate;

        Registration::create([
            'customer_id' => $validated['customer_id'],
            'user_id' => $request->user()->id,
            'attachment_path' => $request->file('attachment')?->store('registration-attachments'),
            ...$this->fields($request, $validated, $hourlyRate),
        ]);

        return redirect()->route('financial.registration')->with('status', __('financial.registration.saved'));
    }

    /**
     * Display the form to change an uninvoiced registration.
     */
    public function edit(Registration $registration): View
    {
        abort_if($registration->invoice_id !== null, 403);

        return view('financial.registration', [
            'registration' => $registration->load('customer'),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'hourly_rate']),
        ]);
    }

    /**
     * Update an uninvoiced registration.
     */
    public function update(Request $request, Registration $registration): RedirectResponse
    {
        abort_if($registration->invoice_id !== null, 403);

        $validated = $request->validate($this->rules());

        $productChanged = (int) $validated['product_id'] !== $registration->product_id;
        $hourlyRate = $productChanged
            ? (float) Product::findOrFail($validated['product_id'])->hourly_rate
            : (float) $registration->hourly_rate;

        $attachmentPath = $registration->attachment_path;

        if ($request->hasFile('attachment')) {
            $this->deleteAttachment($registration);
            $attachmentPath = $request->file('attachment')->store('registration-attachments');
        }

        $registration->update([
            'attachment_path' => $attachmentPath,
            ...$this->fields($request, $validated, $hourlyRate),
        ]);

        return redirect()
            ->route('financial.uninvoiced.show', $registration->customer_id)
            ->with('status', __('financial.registration.updated'));
    }

    /**
     * Delete an uninvoiced registration.
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        abort_if($registration->invoice_id !== null, 403);

        $this->deleteAttachment($registration);
        $registration->delete();

        return redirect()
            ->route('financial.uninvoiced.show', $registration->customer_id)
            ->with('status', __('financial.registration.deleted'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'worked_on' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,gif,png,pdf', 'max:10240'],
            'hours' => ['required', 'numeric', 'min:0.01', 'max:9999', 'decimal:0,2'],
            'vat_rate' => ['required', 'integer', Rule::in([21, 9, 0])],
        ];
    }

    /**
     * Columns shared by create and update, including the calculated amounts.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function fields(Request $request, array $validated, float $hourlyRate): array
    {
        $priceIncludesVat = $request->boolean('price_includes_vat');
        $vatFactor = $validated['vat_rate'] / 100;
        $gross = round($validated['hours'] * $hourlyRate, 2);

        if ($priceIncludesVat) {
            $amountInclVat = $gross;
            $amountExclVat = round($gross / (1 + $vatFactor), 2);
            $vatAmount = round($amountInclVat - $amountExclVat, 2);
        } else {
            $amountExclVat = $gross;
            $vatAmount = round($amountExclVat * $vatFactor, 2);
            $amountInclVat = round($amountExclVat + $vatAmount, 2);
        }

        return [
            'product_id' => $validated['product_id'],
            'worked_on' => $validated['worked_on'],
            'description' => $validated['description'] ?? null,
            'hours' => $validated['hours'],
            'hourly_rate' => $hourlyRate,
            'vat_rate' => $validated['vat_rate'],
            'billable' => $request->boolean('billable'),
            'price_includes_vat' => $priceIncludesVat,
            'amount_excl_vat' => $amountExclVat,
            'vat_amount' => $vatAmount,
            'amount_incl_vat' => $amountInclVat,
        ];
    }

    private function deleteAttachment(Registration $registration): void
    {
        if ($registration->attachment_path !== null) {
            Storage::delete($registration->attachment_path);
        }
    }
}
