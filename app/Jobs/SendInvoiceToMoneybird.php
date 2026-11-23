<?php

namespace App\Jobs;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\MoneybirdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendInvoiceToMoneybird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 300, 900];

    public function __construct(public int $invoiceId) {}

    public function handle(MoneybirdService $moneybird): void
    {
        // The row lock makes a second job for the same invoice wait, then see it is already synced.
        DB::transaction(function () use ($moneybird): void {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($this->invoiceId);

            if ($invoice->status === InvoiceStatus::Synced) {
                return;
            }

            $invoice->load(['customer', 'registrations.product', 'expenses']);
            $customer = $invoice->customer;

            if ($customer->moneybird_contact_id === null) {
                $customer->forceFill(['moneybird_contact_id' => $moneybird->syncCustomer($customer)])->saveQuietly();
            }

            $moneybirdInvoiceId = $moneybird->createSalesInvoice($invoice);

            $invoice->forceFill([
                'status' => InvoiceStatus::Synced,
                'moneybird_invoice_id' => $moneybirdInvoiceId,
                'synced_at' => now(),
                'error' => null,
            ])->save();
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to send invoice to Moneybird.', [
            'invoice_id' => $this->invoiceId,
            'exception' => $exception->getMessage(),
        ]);

        Invoice::query()->whereKey($this->invoiceId)->update([
            'status' => InvoiceStatus::Failed->value,
            'error' => Str::limit($exception->getMessage(), 1000),
        ]);
    }
}
