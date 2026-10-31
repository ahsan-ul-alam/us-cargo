<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->enum('direction', ['uk_to_bd', 'bd_to_uk'])->default('uk_to_bd');
            $table->enum('service_type', ['air_cargo', 'door_to_door', 'parcel'])->default('air_cargo');
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'closed'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
