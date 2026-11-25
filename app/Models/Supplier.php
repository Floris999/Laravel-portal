<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property-read string $supplier_number
 * @property string $name
 * @property string|null $kvk
 * @property string|null $vat_number
 * @property string $administration_email
 * @property string $email
 * @property string|null $phone
 * @property Carbon $supplier_since
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
#[Fillable(['name', 'kvk', 'vat_number', 'administration_email', 'email', 'phone', 'supplier_since', 'address', 'postal_code', 'city', 'country', 'latitude', 'longitude', 'logo_path', 'active', 'moneybird_contact_id'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory, SoftDeletes;

    public const NUMBER_PREFIX = 'L';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'supplier_since' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * The supplier number (L1, L2, ..., L145), derived from the ascending primary key without zero padding.
     */
    public function getSupplierNumberAttribute(): string
    {
        return self::NUMBER_PREFIX . $this->id;
    }
}
