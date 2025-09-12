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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_id')->unique();
            $table->string('title');
            $table->string('supplier_name');
            $table->string('company_name')->nullable();
            $table->string('display_name');
            $table->string('nic')->unique()->nullable();
            $table->text('primary_address');
            $table->string('supplier_mobile');
            $table->string('office_no')->nullable();
            $table->string('fax')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('company_address')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->integer('credit_period')->nullable();
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
        Schema::dropIfExists('suppliers');
    }
};
