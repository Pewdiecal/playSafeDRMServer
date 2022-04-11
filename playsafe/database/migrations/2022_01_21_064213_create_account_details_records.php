<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountDetailsRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_details_records', function (Blueprint $table) {
            $table->id('account_id');
            $table->string('registered_region');
            $table->string('subscribtion_status');
            $table->integer('downloaded_content_qty');
            $table->float('total_streaming_hours');
            $table->integer('loggedIn_device_num');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_details_records');
    }
}
