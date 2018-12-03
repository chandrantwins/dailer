<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //php artisan migrate --path \database\migrations\sprint1
        Schema::create('invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customerid');
            $table->boolean('status');
            $table->text('amount')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('contact_id')->unsigned()->nullable();
                        
            $table->foreign('contact_id')->references('id')->on('contacts');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}
