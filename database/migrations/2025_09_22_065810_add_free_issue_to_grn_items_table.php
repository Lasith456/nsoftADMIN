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
            $table->boolean('is_free_issue')->default(false)->after('discount');
            $table->integer('free_issue_qty')->default(0)->after('is_free_issue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn(['is_free_issue', 'free_issue_qty']);
        });
    }
};
