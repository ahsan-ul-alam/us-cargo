<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // Sender
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address');
            $table->enum('sender_country', ['uk', 'bd'])->default('uk');

            // Recipient
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_address');
            $table->enum('recipient_country', ['uk', 'bd'])->default('bd');

            // Shipment details
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('price_gbp', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->enum('service_type', ['air_cargo', 'door_to_door', 'parcel'])->default('air_cargo');

            // Status
            $table->enum('status', [
                'pending',
                'collected',
                'in_transit',
                'customs_clearance',
                'out_for_delivery',
                'delivered',
                'returned',
                'cancelled',
            ])->default('pending');

            $table->date('estimated_delivery')->nullable();
            $table->date('actual_delivery')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
