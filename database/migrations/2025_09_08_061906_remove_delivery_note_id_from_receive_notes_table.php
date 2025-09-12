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
            $table->dropForeign(['delivery_note_id']);
            $table->dropColumn('delivery_note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receive_notes', function (Blueprint $table) {
            $table->foreignId('delivery_note_id')->constrained()->onDelete('cascade');
        });
    }
};
