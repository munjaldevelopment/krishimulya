<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTestVendor1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tractor_purchase_enquiry_vendor', function ($table) {
            $table->increments('id');
            $table->integer('tractor_purchase_enquiry_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('tractor_purchase_enquiry_id', 'tractor_purchase_enquiry_fk')
                ->references('id')
                ->on('tractor_purchase_enquiry')
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
        Schema::drop('tractor_purchase_enquiry_vendor');
    }
}
