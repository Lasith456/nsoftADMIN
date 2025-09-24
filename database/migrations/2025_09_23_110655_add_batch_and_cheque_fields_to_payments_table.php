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
            // Batch ID to group bulk payments
            $table->string('batch_id')->nullable()->index();

            // Cheque-specific fields
            $table->foreignId('bank_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->date('cheque_received_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn(['batch_id', 'bank_id', 'cheque_number', 'cheque_date', 'cheque_received_date']);
        });
    }

};
