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
        Schema::table('return_notes', function (Blueprint $table) {
            $table->string('status', 50)->default('Pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('return_notes', function (Blueprint $table) {
            $table->boolean('status')->default(0)->change();
        });
    }
};
