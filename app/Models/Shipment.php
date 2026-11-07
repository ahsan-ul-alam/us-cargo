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
        'is_consolidated', 'parcel_count', 'transport_mode',
    ];

    protected $casts = [
        'is_consolidated'    => 'boolean',
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

    /** Label for admin lists/dropdowns where no individual party exists. */
    public function getSenderLabelAttribute(): string
    {
        return $this->is_consolidated ? 'Consolidated shipment' : (string) $this->sender_name;
    }

    public function getRecipientLabelAttribute(): string
    {
        return $this->is_consolidated
            ? ($this->parcel_count ? "{$this->parcel_count} parcels" : 'Multiple recipients')
            : (string) $this->recipient_name;
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->price_gbp - (float) $this->paid_amount);
    }

    /**
     * Status flow (admin label, Bengali public label).
     *   Air: Collected → UK Airport → In Air → Bangladesh Airport → Dhaka/Sylhet Office → In Transit → Delivered
     *   Sea: Collected → UK Port → In Sea → Chattogram Port → Dhaka/Sylhet Office → In Transit → Delivered
     */
    public const STATUSES = [
        'pending'        => ['Pending', 'অপেক্ষমান'],
        'collected'      => ['Collected', 'সংগ্রহ করা হয়েছে'],
        'uk_airport'     => ['Heathrow / UK Airport', 'যুক্তরাজ্য বিমানবন্দরে (হিথ্রো)'],
        'in_air'         => ['In Air', 'আকাশপথে'],
        'bd_airport'     => ['Hazrat Shahjalal International Airport, Dhaka', 'হযরত শাহজালাল আন্তর্জাতিক বিমানবন্দর, ঢাকা'],
        'uk_port'        => ['UK Port', 'যুক্তরাজ্য বন্দরে'],
        'at_sea'         => ['In Sea', 'সমুদ্রপথে'],
        'bd_port'        => ['Chattogram International Port', 'চট্টগ্রাম আন্তর্জাতিক বন্দর'],
        'dhaka_office'   => ['Dhaka Office', 'ঢাকা অফিসে'],
        'sylhet_office'  => ['Sylhet Office', 'সিলেট অফিসে'],
        'in_transit'     => ['In Transit', 'ডেলিভারির পথে'],
        'delivered'      => ['Delivered', 'ডেলিভারি হয়েছে'],
        'returned'       => ['Returned', 'ফেরত'],
        'cancelled'      => ['Cancelled', 'বাতিল'],
    ];

    private const MODE_STATUSES = [
        'air' => ['pending', 'collected', 'uk_airport', 'in_air', 'bd_airport', 'dhaka_office', 'sylhet_office', 'in_transit', 'delivered', 'returned', 'cancelled'],
        'sea' => ['pending', 'collected', 'uk_port', 'at_sea', 'bd_port', 'dhaka_office', 'sylhet_office', 'in_transit', 'delivered', 'returned', 'cancelled'],
    ];

    /** key => label for every known status (filters, charts). */
    public static function allStatusOptions(): array
    {
        return array_map(fn ($s) => $s[0], self::STATUSES);
    }

    /** key => label for the statuses valid for a transport mode ('air' or 'sea'), in flow order. */
    public static function statusOptions(?string $mode = 'air'): array
    {
        $keys = self::MODE_STATUSES[$mode === 'sea' ? 'sea' : 'air'];

        return collect($keys)->mapWithKeys(fn ($k) => [$k => self::STATUSES[$k][0]])->all();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? (string) $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'delivered'                                   => 'success',
            'in_transit', 'in_air', 'at_sea'              => 'info',
            'uk_airport', 'uk_port', 'bd_airport', 'bd_port',
            'dhaka_office', 'sylhet_office'               => 'warning',
            'returned', 'cancelled'                       => 'danger',
            default                                       => 'gray',
        };
    }
}
