<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number', 'invoice_number', 'customer_id', 'manifest_id',
        'sender_name', 'sender_phone', 'sender_address', 'sender_country',
        'recipient_name', 'recipient_phone', 'recipient_address', 'recipient_country',
        'weight_kg', 'price_gbp', 'description', 'service_type',
        'status', 'estimated_delivery', 'actual_delivery', 'admin_notes',
        'payment_status', 'payment_method', 'paid_amount', 'paid_at',
    ];

    protected $casts = [
        'estimated_delivery' => 'date',
        'actual_delivery'    => 'date',
        'weight_kg'          => 'decimal:2',
        'price_gbp'          => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'paid_at'            => 'datetime',
    ];

    public static function generateTrackingNumber(): string
    {
        do {
            $number = 'PC-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('tracking_number', $number)->exists());
        return $number;
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('invoice_number', $number)->exists());
        return $number;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderByDesc('occurred_at');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->price_gbp - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'           => 'অপেক্ষমান',
            'collected'         => 'সংগ্রহ করা হয়েছে',
            'in_transit'        => 'পরিবহনে আছে',
            'customs_clearance' => 'কাস্টমস ক্লিয়ারেন্স',
            'out_for_delivery'  => 'ডেলিভারিতে',
            'delivered'         => 'ডেলিভারি হয়েছে',
            'returned'          => 'ফেরত',
            'cancelled'         => 'বাতিল',
            default             => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'delivered'                        => 'success',
            'in_transit', 'out_for_delivery'   => 'info',
            'customs_clearance'                => 'warning',
            'returned', 'cancelled'            => 'danger',
            default                            => 'gray',
        };
    }
}
