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
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('stamp_fee', 10, 2)->default(0)->after('amount');
            $table->decimal('surcharge_fee', 10, 2)->default(0)->after('stamp_fee');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['stamp_fee', 'surcharge_fee']);
        });
    }
};
