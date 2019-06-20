<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',1000);
            $table->string('thumb_img_url',1024);
            $table->string('video_url',1024);
            $table->string('video_preview_url',1024)->nullable();
            $table->integer('category_id')->default(0);
            $table->integer('status')->default(-1);
            $table->integer('rank')->default(255);
            //$table->integer('vip_type')->default(0);
            $table->timestamps();
            $table->index('category_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
