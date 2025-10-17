<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wastage_logs', function (Blueprint $table) {
            $table->enum('status', ['pending', 'returned'])->default('pending')->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('wastage_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

