<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimezoneoffsetSchedulecallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_calls', function (Blueprint $table) {
            $table->dateTime('originalremindmeat');
            $table->integer('timezoneOffset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_calls', function (Blueprint $table) {
            $table->dropColumn('originalremindmeat');
            $table->dropColumn('timezoneOffset');
        });
    }
}
