<?php

use Illuminate\Database\Migrations\Migration;

class CreatePincodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pincodes', function ($table) {
            $table->increments('id');
            $table->string('city'); // comma separated
            $table->string('state'); // comma separated
            $table->string('zip_code'); // comma separated
            $table->tinyInteger('status');
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
        Schema::drop('pincodes');
    }
}
