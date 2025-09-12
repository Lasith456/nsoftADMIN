<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::dropIfExists('products');

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();
            $table->string('name');
            $table->string('appear_name');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sub_department_id')->nullable()->constrained('sub_departments')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_vat')->default(false);
            $table->integer('units_per_case')->default(1);
            $table->string('unit_of_measure');
            $table->decimal('cost_price', 15, 2)->default(0.00);
            $table->decimal('selling_price', 15, 2)->default(0.00);
            $table->integer('reorder_qty')->default(0);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};