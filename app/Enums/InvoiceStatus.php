<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Concept = 'concept';
    case Sending = 'sending';
    case Synced = 'synced';
    case Failed = 'failed';

    public function label(): string
    {
        return __('financial.invoices.status.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Concept => 'zinc',
            self::Sending => 'amber',
            self::Synced => 'lime',
            self::Failed => 'red',
        };
    }

    /**
     * Whether the invoice can still be sent to Moneybird or put back to uninvoiced.
     */
    public function isActionable(): bool
    {
        return $this === self::Concept || $this === self::Failed;
    }

    /**
     * Whether sending to Moneybird can be tried again (failed, or stuck while sending).
     */
    public function canRetry(): bool
    {
        return $this === self::Sending || $this === self::Failed;
    }

    /**
     * Whether the invoice is in Moneybird and can only still be deleted here.
     */
    public function isDeletable(): bool
    {
        return $this === self::Synced;
    }
}
