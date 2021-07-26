<?php

use Illuminate\Database\Migrations\Migration;

class CreateCheckInOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_checkin_outs', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->datetime('checkin_time');
            $table->datetime('checkout_time');
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
