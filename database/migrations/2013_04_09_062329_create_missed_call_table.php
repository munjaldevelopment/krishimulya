<?php

use Illuminate\Database\Migrations\Migration;

class CreateMissedCallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missed_calls', function ($table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->string('caller_id');
            $table->string('received_id');
            $table->string('duration');
            $table->text('recording_url');
            $table->string('call_type');
            $table->string('call_status');
            $table->datetime('datetime');
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
