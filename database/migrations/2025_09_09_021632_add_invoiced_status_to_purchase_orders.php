<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Update the column to include the new status option.
            $table->string('status')->default('pending')->comment('e.g., pending, processing, delivered, cancelled, rejected, invoiced')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('e.g., pending, processing, delivered, cancelled, rejected')->change();
        });
    }
};
