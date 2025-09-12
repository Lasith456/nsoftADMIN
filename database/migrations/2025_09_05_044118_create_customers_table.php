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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('title');
            $table->string('customer_name');
            $table->string('company_name')->nullable();
            $table->string('display_name');
            $table->string('nic')->unique()->nullable();
            $table->text('primary_address');
            $table->string('customer_mobile');
            $table->string('customer_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('company_address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->text('remark')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
