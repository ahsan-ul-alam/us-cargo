<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manifest extends Model
{
    protected $fillable = [
        'manifest_number', 'transport_type', 'carrier_name', 'flight_or_vessel_number',
        'direction', 'departure_date', 'arrival_date', 'status', 'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date'   => 'date',
    ];

    public static function generateManifestNumber(): string
    {
        do {
            $number = 'MF-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('manifest_number', $number)->exists());
        return $number;
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->shipments()->sum('weight_kg');
    }
}
