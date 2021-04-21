<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1Vendor9Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agri_tool_enquiry_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('agri_tool_enquiry_id')->unsigned();
            $table->integer('agri_tool_enquiry_vendor_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('agri_tool_enquiry_id', 'agri_tool_enquiry_fk')
                ->references('id')
                ->on('agri_tool_enquiry')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('agri_tool_enquiry_vendor_id', 'agri_tool_enquiry1_fk')
                ->references('id')
                ->on('agri_tool_enquiry_vendor')
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
        Schema::drop('agri_tool_enquiry_vendor_history');
    }
}
