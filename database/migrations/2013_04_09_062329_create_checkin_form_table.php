<?php

use Illuminate\Database\Migrations\Migration;

class CreateCheckInFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_checkin_forms', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('users_checkin_out_id');
            
            $table->foreign('users_checkin_out_id')
                ->references('id')
                ->on('users_checkin_outs')
                ->onDelete('cascade');

            $table->string('customer_name');
            $table->string('mobile_number');
            $table->unsignedBigInteger('call_type_id');

            $table->foreign('call_type_id')
                ->references('id')
                ->on('call_types')
                ->onDelete('cascade');

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
