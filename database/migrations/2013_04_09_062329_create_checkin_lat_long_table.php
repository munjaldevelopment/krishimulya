<?php

use Illuminate\Database\Migrations\Migration;

class CreateCheckInLatLongTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_checkin_lat_long', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('user_lat');
            $table->string('user_long');

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
        Schema::drop('app_popups');
    }
}
