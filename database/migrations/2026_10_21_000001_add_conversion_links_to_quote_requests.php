<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_normalized')->nullable()->index();
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            // unique: one quote can only ever produce one shipment
            $table->foreignId('shipment_id')->nullable()->unique()->constrained()->nullOnDelete();
        });

        DB::table('customers')->select('id', 'phone')->orderBy('id')->each(function ($c) {
            DB::table('customers')->where('id', $c->id)
                ->update(['phone_normalized' => Customer::normalizePhone($c->phone)]);
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_id');
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('phone_normalized');
        });
    }
};
