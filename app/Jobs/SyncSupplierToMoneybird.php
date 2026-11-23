<?php

namespace App\Jobs;

use App\Models\Supplier;
use App\Services\MoneybirdService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSupplierToMoneybird implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 300, 900];

    public function __construct(public int $supplierId) {}

    public function handle(MoneybirdService $moneybird): void
    {
        $supplier = Supplier::withTrashed()->findOrFail($this->supplierId);
        $contactId = $moneybird->syncSupplier($supplier);

        if ($supplier->moneybird_contact_id !== $contactId) {
            $supplier->forceFill(['moneybird_contact_id' => $contactId])->saveQuietly();
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Failed to synchronize supplier with Moneybird.', [
            'supplier_id' => $this->supplierId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
