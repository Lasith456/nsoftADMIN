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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_no')->unique();
            $table->string('vehicle_id')->unique();
            
            // Driver Details
            $table->string('title');
            $table->string('driver_name');
            $table->string('driver_nic')->unique()->nullable();
            $table->text('driver_address');
            $table->string('driver_mobile');

            // Assistant Details
            $table->string('assistant_name')->nullable();
            $table->string('assistant_mobile')->nullable();
            $table->string('assistant_nic')->unique()->nullable();
            $table->text('assistant_address')->nullable();

            // Other Details
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
        Schema::dropIfExists('vehicles');
    }
};
