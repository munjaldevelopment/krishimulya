<?php

use Illuminate\Database\Migrations\Migration;

class CreateCallTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_types', function ($table) {
            $table->increments('id');
            $table->string('type_name');
            $table->text('description');
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
        Schema::drop('call_types');
    }
}
