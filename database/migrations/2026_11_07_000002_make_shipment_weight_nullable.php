<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('weight_kg', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Intentionally left as nullable: restoring NOT NULL would fail on rows without a weight.
    }
};
