<?php

use Illuminate\Database\Migrations\Migration;

class CreateSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surveys', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('customer_name');
            $table->string('mobile_number');
            $table->string('land_size');
            $table->unsignedBigInteger('crop_type_id');

            $table->foreign('crop_type_id')
                ->references('id')
                ->on('crop_types')
                ->onDelete('cascade');

            $table->string('last_production');
            $table->string('earning_sale');
            $table->string('user_long');

            $table->string('proposed_crop');
            $table->string('tractor');
            $table->string('tractor_make')->nullable();
            $table->string('tractor_model')->nullable();
            $table->string('tractor_finance_free')->nullable();
            $table->string('tractor_cultivation')->nullable();

            $table->string('rental_price')->nullable();
            $table->string('rent_taken_from')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('contact_details')->nullable();

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
        Schema::drop('app_popups');
    }
}
