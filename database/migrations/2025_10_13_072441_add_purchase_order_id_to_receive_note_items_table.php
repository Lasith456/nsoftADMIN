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
        Schema::table('receive_note_items', function (Blueprint $table) {
            // ✅ Add the purchase_order_id column (nullable for safety)
            if (!Schema::hasColumn('receive_note_items', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('receive_note_id');

                // ✅ Add foreign key reference
                $table->foreign('purchase_order_id')
                      ->references('id')
                      ->on('purchase_orders')
                      ->nullOnDelete(); // set to NULL if PO deleted
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receive_note_items', function (Blueprint $table) {
            if (Schema::hasColumn('receive_note_items', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
        });
    }
};
