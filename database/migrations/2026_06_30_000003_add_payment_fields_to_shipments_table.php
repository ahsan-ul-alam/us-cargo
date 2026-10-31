<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('tracking_number');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('price_gbp');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'other'])->nullable()->after('payment_status');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_status', 'payment_method', 'paid_amount', 'paid_at']);
        });
    }
};
