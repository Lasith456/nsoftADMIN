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
        Schema::table('grn_items', function (Blueprint $table) {
            $table->string('stock_type')->after('unit_type'); // clear or non-clear
            $table->string('serial')->nullable()->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn(['stock_type', 'serial']);
        });
    }
};