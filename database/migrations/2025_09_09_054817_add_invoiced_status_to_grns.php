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
        Schema::table('grns', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('e.g., pending, confirmed, cancelled, invoiced')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('e.g., pending, confirmed, cancelled')->change();
        });
    }
};