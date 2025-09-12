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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('agent_id')->unique();
            $table->string('name');
            
            // Foreign key to connect with the products table
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->string('contact_no');
            $table->text('address');
            $table->string('email')->nullable();
            $table->integer('credit_period')->nullable()->comment('In days');
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            
            // Agent-specific product pricing
            $table->decimal('price_per_case', 15, 2);
            $table->integer('units_per_case');
            $table->string('unit_of_measure');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};