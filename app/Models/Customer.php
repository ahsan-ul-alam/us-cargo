<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp',
        'address_uk', 'address_bd', 'country', 'notes',
    ];

    protected static function booted(): void
    {
        static::saving(function (Customer $customer) {
            $customer->phone_normalized = static::normalizePhone($customer->phone);
        });
    }

    /**
     * Reduce a phone number to its national digits so "+44 7700 900123",
     * "07700-900123" and "00447700900123" compare equal (same for +880 / 0).
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        foreach (['44', '880'] as $cc) {
            if (str_starts_with($digits, $cc) && strlen($digits) > strlen($cc) + 8) {
                return ltrim(substr($digits, strlen($cc)), '0');
            }
        }

        return ltrim($digits, '0');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
