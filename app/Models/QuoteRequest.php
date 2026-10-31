<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'weight_kg',
        'direction', 'service_type', 'description',
        'status', 'admin_notes', 'customer_id', 'shipment_id',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
