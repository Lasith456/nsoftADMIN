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
            $table->unsignedBigInteger('receive_note_id')->nullable()->after('customer_id');
            $table->foreign('receive_note_id')->references('id')->on('receive_notes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('return_notes', function (Blueprint $table) {
            $table->dropForeign(['receive_note_id']);
            $table->dropColumn('receive_note_id');
        });
    }

};
