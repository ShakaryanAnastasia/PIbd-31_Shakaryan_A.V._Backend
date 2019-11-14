<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['id']);
        });
    }
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['id']);
        });
    }
}
