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
        Schema::create('banks', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name')->unique(); // The full name of the bank, e.g., "Commercial Bank of Ceylon"
            $table->string('code')->nullable()->unique(); // A short code for the bank, e.g., "COMBANK"
            $table->boolean('is_active')->default(true); // Status to enable/disable the bank
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};