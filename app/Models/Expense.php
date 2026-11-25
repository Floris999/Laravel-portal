<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $user_id
 * @property int|null $invoice_id
 * @property int|null $category_id
 * @property Carbon $spent_on
 * @property string $description
 * @property string|null $attachment_path
 * @property string $amount
 * @property int $vat_rate
 * @property bool $billable
 * @property bool $price_includes_vat
 * @property string $amount_excl_vat
 * @property string $vat_amount
 * @property string $amount_incl_vat
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'customer_id',
    'user_id',
    'invoice_id',
    'category_id',
    'spent_on',
    'description',
    'attachment_path',
    'amount',
    'vat_rate',
    'billable',
    'price_includes_vat',
    'amount_excl_vat',
    'vat_amount',
    'amount_incl_vat',
])]
class Expense extends Model
{

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spent_on' => 'date',
            'amount' => 'decimal:2',
            'vat_rate' => 'integer',
            'billable' => 'boolean',
            'price_includes_vat' => 'boolean',
            'amount_excl_vat' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'amount_incl_vat' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<Expense>  $query
     * @return Builder<Expense>
     */
    public function scopeUninvoiced(Builder $query): Builder
    {
        return $query->whereNull('invoice_id');
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
