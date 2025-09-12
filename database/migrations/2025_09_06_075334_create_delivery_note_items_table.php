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
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity_requested');
            $table->integer('quantity_from_stock')->default(0);
            
            // This will store the ID of the agent chosen to fulfill the shortage
            $table->foreignId('agent_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity_from_agent')->default(0);

            $table->string('product_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
