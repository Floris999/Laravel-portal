<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $kvk
 * @property string|null $vat_number
 * @property string $administration_email
 * @property string $email
 * @property string|null $phone
 * @property Carbon $customer_since
 * @property string $address
 * @property string|null $postal_code
 * @property string $city
 * @property string $country
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $logo_path
 * @property bool $active
 * @property string|null $moneybird_contact_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'kvk', 'vat_number', 'administration_email', 'email', 'phone', 'customer_since', 'address', 'postal_code', 'city', 'country', 'latitude', 'longitude', 'logo_path', 'active', 'moneybird_contact_id'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'customer_since' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
