<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'direction', 'service_type', 'price_per_kg', 'min_weight_kg',
        'currency', 'is_active', 'notes',
    ];

    /** Price for a weight using the active rate, honouring the minimum chargeable weight. Null if no rate/weight. */
    public static function priceFor(?string $direction, ?string $serviceType, $weightKg): ?float
    {
        if (! $direction || ! $serviceType || ! is_numeric($weightKg) || (float) $weightKg <= 0) {
            return null;
        }

        $rate = static::where('is_active', true)
            ->where('direction', $direction)
            ->where('service_type', $serviceType)
            ->first();

        if (! $rate) {
            return null;
        }

        $chargeable = max((float) $weightKg, (float) $rate->min_weight_kg);

        return round($chargeable * (float) $rate->price_per_kg, 2);
    }

    protected $casts = [
        'price_per_kg'  => 'decimal:2',
        'min_weight_kg' => 'decimal:2',
        'is_active'     => 'boolean',
    ];
}
