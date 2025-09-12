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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('type')->default('standard')->after('invoice_id'); // e.g., standard, vat, non-vat
            $table->decimal('sub_total', 15, 2)->after('due_date');
            $table->decimal('vat_percentage', 5, 2)->default(0.00)->after('sub_total');
            $table->decimal('vat_amount', 15, 2)->default(0.00)->after('vat_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['type', 'sub_total', 'vat_percentage', 'vat_amount']);
        });
    }
};