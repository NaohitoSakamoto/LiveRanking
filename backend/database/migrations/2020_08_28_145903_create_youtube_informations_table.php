<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYoutubeInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('youtube_informations', function (Blueprint $table) {
            $table->id();
            $table->string('videoID');
            $table->string('channelID');
            $table->string('channelTitle');
            $table->string('channelThumbnail');
            $table->string('country');
            $table->string('videoTitle');
            $table->string('videoDescription');
            $table->string('videoThumbnail');
            $table->integer('concurrentViewer');
            $table->string('actualStartTime');
            $table->timestamps();
            $table->index('concurrentViewer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('youtube_informations');
    }
}
