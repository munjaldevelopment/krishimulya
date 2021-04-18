<?php

use Illuminate\Database\Migrations\Migration;

class CreateVendorServiceAssignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_service_assign', function ($table) {
            $table->increments('id');
            $table->integer('vendor_id')->unsigned();
            $table->integer('vendor_service_id')->unsigned();
            $table->text('zip_code'); // comma separated
            $table->string('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vendor_service_assign');
    }
}
