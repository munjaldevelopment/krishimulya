<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1Vendor4Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tractor_sell_enquiry_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('tractor_sell_enquiry_id')->unsigned();
            $table->integer('tractor_sell_enquiry_vendor_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('tractor_sell_enquiry_id', 'tractor_sell_enquiry_fk')
                ->references('id')
                ->on('tractor_sell_enquiry')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('tractor_sell_enquiry_vendor_id', 'tractor_sell_enquiry_vendor_fk')
                ->references('id')
                ->on('tractor_sell_enquiry_vendor')
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
        Schema::drop('tractor_sell_enquiry_vendor_history');
    }
}
