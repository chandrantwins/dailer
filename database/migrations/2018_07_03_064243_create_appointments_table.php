<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'appointments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('phoneNumber');
                $table->dateTime('when');
                $table->integer('timezoneOffset');
                $table->dateTime('notificationTime');
				$table->dateTime('originalnotificationTime');
				$table->integer('created_by');
				$table->integer('assigned_to');
				$table->integer('contact_id');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('appointments');
    }
}
