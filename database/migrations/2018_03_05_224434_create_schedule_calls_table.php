<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('remind_me_at')->default(0);
            $table->boolean('email_sent')->default(false);
            $table->integer('call_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('call_id')->references('id')->on('calls');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_calls');
    }
}
