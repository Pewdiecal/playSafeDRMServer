<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_records', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('account_id');
            $table->string('username');
            $table->string('email');
            $table->string('password');
            $table->boolean('is_content_provider')->default(false);
            $table->foreign('account_id')->references('account_id')->on('account_details_records');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_records');
    }
}
