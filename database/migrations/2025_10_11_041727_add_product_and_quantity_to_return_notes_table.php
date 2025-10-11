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
    Schema::table('return_notes', function (Blueprint $table) {
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->integer('quantity')->nullable();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_notes', function (Blueprint $table) {
            //
        });
    }
};
