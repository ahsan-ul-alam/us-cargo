<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->enum('transport_type', ['air', 'sea'])->default('air');
            $table->string('carrier_name')->nullable();
            $table->string('flight_or_vessel_number')->nullable();
            $table->enum('direction', ['uk_to_bd', 'bd_to_uk'])->default('uk_to_bd');
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->enum('status', ['preparing', 'departed', 'arrived', 'cleared'])->default('preparing');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('manifest_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manifest_id');
        });

        Schema::dropIfExists('manifests');
    }
};
