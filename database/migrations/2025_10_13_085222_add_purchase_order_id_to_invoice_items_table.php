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
        Schema::table('invoice_items', function (Blueprint $table) {
            // 🟢 Add the purchase_order_id column only if it doesn't exist
            if (!Schema::hasColumn('invoice_items', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')
                      ->nullable()
                      ->after('invoice_id') // place it neatly after RN ID
                      ->constrained('purchase_orders')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
        });
    }
};
