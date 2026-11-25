<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Supplier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use UnexpectedValueException;

class MoneybirdService
{
    /**
     * Fetch categories from Moneybird and sync them to the database.
     *
     * @return int Number of categories synced
     */
    public function syncCategories(): int
    {
        [$request, $administrationId] = $this->connection();

        $response = $request->get("{$administrationId}/ledger_accounts.json")->throw();
        $categories = $response->json();

        $synced = 0;

        foreach ($categories as $category) {
            $categoryId = (string) $category['id'];

            Category::updateOrCreate(
                ['moneybird_id' => $categoryId],
                [
                    'name' => $category['name'],
                    'account_type' => $category['account_type'],
                    'account_id' => $category['account_id'] ?? null,
                    'active' => $category['active'],
                    'taxonomy_item' => $category['taxonomy_item'] ?? null,
                ]
            );

            $synced++;
        }

        return $synced;
    }

    /**
     * Check whether the configured API token and administration ID give access to Moneybird.
     *
     * @return array{connected: bool, message: string|null}
     */
    public function checkConnection(): array
    {
        try {
            [$request, $administrationId] = $this->connection();
        } catch (RuntimeException) {
            return ['connected' => false, 'message' => __('settings.moneybird.errors.not_configured')];
        }

        try {
            $response = $request->get("{$administrationId}/tax_rates.json", ['per_page' => 1]);
        } catch (ConnectionException) {
            return ['connected' => false, 'message' => __('settings.moneybird.errors.unreachable')];
        }

        return match (true) {
            $response->successful() => ['connected' => true, 'message' => null],
            $response->status() === 401 => ['connected' => false, 'message' => __('settings.moneybird.errors.invalid_token')],
            in_array($response->status(), [403, 404], true) => ['connected' => false, 'message' => __('settings.moneybird.errors.no_access')],
            default => ['connected' => false, 'message' => __('settings.moneybird.errors.unexpected', ['status' => $response->status()])],
        };
    }

    /**
     * Create a Moneybird contact or update the existing contact for this customer.
     */
    public function syncCustomer(Customer $customer): string
    {
        return $this->syncContact($customer, (string) $customer->id);
    }

    /**
     * Create a Moneybird contact or update the existing contact for this supplier.
     * Suppliers are sent to Moneybird as customers, identified by their own L-prefixed number.
     */
    public function syncSupplier(Supplier $supplier): string
    {
        return $this->syncContact($supplier, $supplier->supplier_number);
    }

    private function syncContact(Customer|Supplier $customer, string $externalId): string
    {
        [$request, $administrationId] = $this->connection();

        $contact = ['contact' => $this->contactData($customer, $externalId)];
        $path = "{$administrationId}/contacts";

        if ($customer->moneybird_contact_id === null) {
            $response = $request->post("{$path}.json", $contact)->throw();
            $contactId = $response->json('id');

            if (! is_string($contactId) && ! is_int($contactId)) {
                throw new RuntimeException('Moneybird did not return a contact ID.');
            }

            return (string) $contactId;
        }

        $request->put("{$path}/{$customer->moneybird_contact_id}.json", $contact)->throw();

        return $customer->moneybird_contact_id;
    }

    /**
     * Create a draft sales invoice in Moneybird, with one invoice line per registration and expense.
     */
    public function createSalesInvoice(Invoice $invoice): string
    {
        $contactId = $invoice->customer->moneybird_contact_id;

        if ($contactId === null) {
            throw new RuntimeException('The customer has no Moneybird contact yet.');
        }

        [$request, $administrationId] = $this->connection();
        $taxRateIds = $this->salesTaxRateIds($request, $administrationId);

        $details = $invoice->registrations
            ->sortBy('worked_on')
            ->map(fn(Registration $registration) => [
                'description' => $this->lineDescription($registration),
                'amount' => (string) (float) $registration->hours,
                'price' => $registration->hourly_rate,
                'tax_rate_id' => $taxRateIds[$registration->vat_rate]
                    ?? throw new RuntimeException("No Moneybird sales tax rate found for {$registration->vat_rate}%."),
            ])
            ->concat($invoice->expenses->sortBy('spent_on')->map(fn(Expense $expense) => [
                'description' => "{$expense->description} - {$expense->spent_on->format('d-m-Y')}",
                'amount' => '1',
                'price' => $expense->amount,
                'tax_rate_id' => $taxRateIds[$expense->vat_rate]
                    ?? throw new RuntimeException("No Moneybird sales tax rate found for {$expense->vat_rate}%."),
            ]))
            ->values()
            ->all();

        $response = $request->post("{$administrationId}/sales_invoices.json", [
            'sales_invoice' => [
                'contact_id' => $contactId,
                'reference' => __('financial.invoices.moneybird_reference', ['period' => $invoice->period->translatedFormat('F Y')]),
                'invoice_date' => now()->toDateString(),
                'prices_are_incl_tax' => $invoice->price_includes_vat,
                'details_attributes' => $details,
            ],
        ])->throw();

        $invoiceId = $response->json('id');

        if (! is_string($invoiceId) && ! is_int($invoiceId)) {
            throw new RuntimeException('Moneybird did not return a sales invoice ID.');
        }

        return (string) $invoiceId;
    }

    /**
     * Active sales tax rates keyed by percentage; the first rate per percentage wins.
     *
     * @return array<int, string>
     */
    private function salesTaxRateIds(PendingRequest $request, string $administrationId): array
    {
        $rates = $request->get("{$administrationId}/tax_rates.json", [
            'filter' => 'tax_rate_type:sales_invoice,active:true',
            'per_page' => 100,
        ])->throw()->json();

        $ids = [];

        foreach ($rates as $rate) {
            $ids[(int) round((float) $rate['percentage'])] ??= (string) $rate['id'];
        }

        return $ids;
    }

    private function lineDescription(Registration $registration): string
    {
        $description = "{$registration->product->name} - {$registration->worked_on->format('d-m-Y')}";

        return $registration->description === null || $registration->description === ''
            ? $description
            : "{$description} - {$registration->description}";
    }

    /**
     * @return array{0: PendingRequest, 1: string}
     */
    private function connection(): array
    {
        $apiToken = config('services.moneybird.api_token');
        $administrationId = config('services.moneybird.administration_id');
        $baseUrl = config('services.moneybird.base_url');

        if (! is_string($apiToken) || $apiToken === '' || ! is_string($administrationId) || $administrationId === '') {
            throw new RuntimeException('Moneybird API token and administration ID must be configured.');
        }

        if (! is_string($baseUrl) || $baseUrl === '') {
            throw new RuntimeException('Moneybird API base URL must be configured.');
        }

        $request = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->withToken($apiToken)
            ->timeout(15);

        return [$request, $administrationId];
    }

    /**
     * @return array<string, string|null>
     */
    private function contactData(Customer|Supplier $customer, string $externalId): array
    {
        return [
            'customer_id' => $externalId,
            'company_name' => $customer->name,
            'address1' => $customer->address,
            'zipcode' => $customer->postal_code,
            'city' => $customer->city,
            'country' => match ($customer->country) {
                'Nederland' => 'NL',
                'België' => 'BE',
                'Duitsland' => 'DE',
                'Overig' => null,
                default => throw new UnexpectedValueException('Unsupported customer country for Moneybird.'),
            },
            'phone' => $customer->phone,
            'email' => $customer->administration_email,
            'send_invoices_to_email' => $customer->email,
            'chamber_of_commerce' => $customer->kvk,
            'tax_number' => $customer->vat_number,
        ];
    }
}
