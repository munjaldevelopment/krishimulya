<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1Vendor5Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_enquiry_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('insurance_enquiry_id')->unsigned();
            $table->integer('insurance_enquiry_vendor_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('insurance_enquiry_id', 'insurance_enquiry_fk')
                ->references('id')
                ->on('insurance_enquiry')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('insurance_enquiry_vendor_id', 'insurance_enquiry_vendor_fk')
                ->references('id')
                ->on('insurance_enquiry_vendor')
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
        Schema::drop('insurance_enquiry_vendor_history');
    }
}
