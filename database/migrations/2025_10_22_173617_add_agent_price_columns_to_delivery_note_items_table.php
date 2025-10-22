<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_note_items', 'agent_price_per_case')) {
                $table->decimal('agent_price_per_case', 10, 2)->nullable()->after('agent_id');
            }
            if (!Schema::hasColumn('delivery_note_items', 'agent_total_price')) {
                $table->decimal('agent_total_price', 12, 2)->nullable()->after('agent_price_per_case');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->dropColumn(['agent_price_per_case', 'agent_total_price']);
        });
    }
};
