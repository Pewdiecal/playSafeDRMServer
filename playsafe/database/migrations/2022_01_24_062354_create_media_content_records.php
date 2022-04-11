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
            $table->string('genre');
            $table->string('directory_name');
            $table->unsignedBigInteger('license_id');
            $table->string('content_description');
            $table->string('available_regions');
            $table->boolean('is_available_offline');
            $table->string('content_cover_art_url');
            $table->unsignedBigInteger('content_provider_id');
            $table->string('max_quality_premium');
            $table->string('max_quality_standard');
            $table->string('max_quality_basic');
            $table->string('max_quality_budget');
            $table->string('max_quality_premiumTrial');
            $table->string('master_playlist_url_1080p')->nullable();
            $table->string('master_playlist_url_720p')->nullable();
            $table->string('master_playlist_url_480p')->nullable();
            $table->string('master_playlist_url_360p')->nullable();
            $table->string('master_playlist_url_240p')->nullable();
            $table->string('master_playlist_url_144p')->nullable();
            $table->foreign('license_id')->references('license_id')->on('content_license_records');
            $table->foreign('content_provider_id')->references('user_id')->on('user_records');
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
