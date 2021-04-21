<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1Vendor10Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agri_type_enquiry_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('agri_type_enquiry_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('agri_type_enquiry_id', 'agri_type_enquiry_fk')
                ->references('id')
                ->on('agri_type_enquiry')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('agri_type_enquiry_vendor_id', 'agri_type_enquiry_vendor_fk')
                ->references('id')
                ->on('agri_type_enquiry_vendor')
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
        Schema::drop('agri_type_enquiry_vendor_history');
    }
}
