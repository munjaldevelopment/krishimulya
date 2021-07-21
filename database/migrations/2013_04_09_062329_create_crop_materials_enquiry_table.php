<?php

use Illuminate\Database\Migrations\Migration;

class CreateCropMaterialsEnquiryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crop_materials_enquiry', function ($table) {
            $table->increments('id');
            $table->string('crop_material');
            $table->string('image');
            $table->text('description');
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
        Schema::drop('crop_materials_enquiry');
    }
}
