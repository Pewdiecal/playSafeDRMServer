<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaContentRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_content_records', function (Blueprint $table) {
            $table->id('content_id');
            $table->string('content_name');
            $table->string('file_format');
            $table->unsignedBigInteger('license_id');
            $table->string('content_description');
            $table->string('available_regions');
            $table->boolean('is_available_offline');
            $table->string('content_cover_art_url');
            $table->foreign('license_id')->references('license_id')->on('content_license_records');
            $table->foreign('content_provider_id')->references('user-id')->on('user_records');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_content_records');
    }
}
