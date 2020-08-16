<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LiveRankings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('live_name', 100);
            $table->string('live_detail', 100);
            $table->string('live_thumbnail', 100);
            $table->string('live_url', 100);
            $table->string('channel_name', 100);
            $table->string('channel_thumbnail', 100);
            $table->string('channel_url', 100);
            $table->integer('live_viewers');
            $table->dateTime('live_start');	
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
        Schema::dropIfExists('live_rankings');
    }
}
