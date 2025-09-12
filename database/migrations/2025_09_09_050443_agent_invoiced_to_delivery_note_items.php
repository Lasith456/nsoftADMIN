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
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->boolean('agent_invoiced')->default(false)->after('quantity_from_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn('agent_invoiced');
        });
    }
};