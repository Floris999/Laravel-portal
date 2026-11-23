<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\MoneybirdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncCustomerToMoneybird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 300, 900];

    public function __construct(public int $customerId) {}

    public function handle(MoneybirdService $moneybird): void
    {
        $customer = Customer::withTrashed()->findOrFail($this->customerId);
        $contactId = $moneybird->syncCustomer($customer);

        if ($customer->moneybird_contact_id !== $contactId) {
            $customer->forceFill(['moneybird_contact_id' => $contactId])->saveQuietly();
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to synchronize customer with Moneybird.', [
            'customer_id' => $this->customerId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
