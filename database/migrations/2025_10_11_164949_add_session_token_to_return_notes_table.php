<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('return_notes', function (Blueprint $table) {
            $table->string('session_token')->nullable()->index()->after('receive_note_id');
        });
    }

    public function down()
    {
        Schema::table('return_notes', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }

};
