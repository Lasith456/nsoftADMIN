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
        // First drop the foreign key in products
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_department_id']);
            $table->dropColumn('sub_department_id');
        });

        // Then drop the sub_departments table
        Schema::dropIfExists('sub_departments');
    }

    public function down(): void
    {
        // Recreate sub_departments table if rolled back
        Schema::create('sub_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Re-add sub_department_id column to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sub_department_id')
                ->nullable()
                ->constrained('sub_departments')
                ->onDelete('set null');
        });
    }

};
