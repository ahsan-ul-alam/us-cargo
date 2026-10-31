<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['uk_to_bd', 'bd_to_uk'])->default('uk_to_bd');
            $table->enum('service_type', ['air_cargo', 'door_to_door', 'parcel']);
            $table->decimal('price_per_kg', 8, 2);
            $table->decimal('min_weight_kg', 8, 2)->default(1);
            $table->string('currency', 3)->default('GBP');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['direction', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
