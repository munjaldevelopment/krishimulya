<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1VendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('soil_test_order_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('soil_test_order_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('soil_test_order_id', 'soil_test_order_fk')
                ->references('id')
                ->on('soil_test_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('soil_test_order_vendor_id', 'soil_test_order_vendor_fk')
                ->references('id')
                ->on('soil_test_order_vendors')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('vendor_id', 'vendor_fk')
                ->references('id')
                ->on('vendors')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('test_status');
            $table->datetime('status_time');


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
        Schema::drop('soil_test_order_vendor_history');
    }
}
