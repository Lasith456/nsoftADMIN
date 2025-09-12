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
        Schema::table('receive_notes', function (Blueprint $table) {
            // Update the status column to include the new 'invoiced' option
            $table->string('status')->default('completed')->comment("e.g., completed, discrepancy, invoiced")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receive_notes', function (Blueprint $table) {
            $table->string('status')->default('completed')->comment("e.g., completed, discrepancy")->change();
        });
    }
};