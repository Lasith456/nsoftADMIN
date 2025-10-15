<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('debit_note_id')->unique(); // e.g. DN-0001
            $table->decimal('amount', 12, 2);
            $table->decimal('used_amount', 12, 2)->default(0);
            $table->enum('status', ['unused', 'partially-used', 'used'])->default('unused');
            $table->text('reason')->nullable();
            $table->date('issued_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
