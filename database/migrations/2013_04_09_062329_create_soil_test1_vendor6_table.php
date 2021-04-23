<?php

use Illuminate\Database\Migrations\Migration;

class CreateSoilTest1Vendor6Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agriland_rent_enquiry_vendor_history', function ($table) {
            $table->increments('id');
            $table->integer('agriland_rent_enquiry_id')->unsigned();
            $table->integer('agriland_rent_enquiry_vendor_id')->unsigned();
            $table->integer('vendor_id')->unsigned();

            $table->foreign('agriland_rent_enquiry_id', 'agriland_rent_enquiry_fk')
                ->references('id')
                ->on('agriland_rent_enquiry')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('agriland_rent_enquiry_vendor_id', 'agriland_rent_enquiry_vendor_fk')
                ->references('id')
                ->on('agriland_rent_enquiry_vendor')
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
        Schema::drop('agriland_rent_enquiry_vendor_history');
    }
}
