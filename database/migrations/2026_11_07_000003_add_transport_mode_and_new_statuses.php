<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('transport_mode', 10)->default('air')->after('service_type');
            // Was an enum; the status flow now differs for air/sea, so allow any known status string.
            $table->string('status', 30)->default('pending')->change();
        });

        // Retire the old statuses.
        DB::table('shipments')->where('status', 'customs_clearance')->update(['status' => 'bd_airport']);
        DB::table('shipments')->where('status', 'out_for_delivery')->update(['status' => 'in_transit']);
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('transport_mode');
        });
    }
};
