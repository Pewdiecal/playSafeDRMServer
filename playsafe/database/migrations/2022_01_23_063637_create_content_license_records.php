<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentLicenseRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_license_records', function (Blueprint $table) {
            $table->id('license_id');
            $table->string('key_id');
            $table->string('label');
            $table->string('private_key');
            $table->string('public_key');
            $table->dateTime('validity_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_license_records');
    }
}
