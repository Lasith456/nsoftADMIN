<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // === MAIN GRN PO TABLE ===
        Schema::create('grn_pos', function (Blueprint $table) {
            $table->id();
            $table->string('grnpo_id')->unique(); // Auto-generated ID like GRNPO-0001
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->date('delivery_date');
            $table->string('status')->default('pending'); // pending | confirmed | cancelled
            $table->timestamps();
        });

        // === GRN PO ITEMS TABLE ===
        Schema::create('grn_po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grnpo_id')->constrained('grn_pos')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // ✅ Correct table name here
        Schema::dropIfExists('grn_po_items');
        Schema::dropIfExists('grn_pos');
    }
};
