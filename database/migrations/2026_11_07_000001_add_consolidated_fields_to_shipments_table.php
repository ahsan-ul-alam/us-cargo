<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->boolean('is_consolidated')->default(false)->after('customer_id');
            $table->unsignedInteger('parcel_count')->nullable()->after('is_consolidated');

            // Consolidated (multi-person) shipments carry no individual party details.
            $table->string('sender_name')->nullable()->change();
            $table->string('sender_phone')->nullable()->change();
            $table->string('sender_address')->nullable()->change();
            $table->string('recipient_name')->nullable()->change();
            $table->string('recipient_phone')->nullable()->change();
            $table->string('recipient_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['is_consolidated', 'parcel_count']);
        });
    }
};
