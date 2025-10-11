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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_categorized')->default(false)->after('status');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->after('is_categorized');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['is_categorized', 'category_id']);
        });
    }

};
