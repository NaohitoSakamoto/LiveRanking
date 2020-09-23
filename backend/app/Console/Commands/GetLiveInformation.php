<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\HandleYoutubeAPI;
use App\HandleYoutubeDB;

class GetLiveInformation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetLiveInformation:Youtube';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	$liveData = array();
	$handleYoutubeAPI = new HandleYoutubeAPI();
	$handleYoutubeAPI->APIKeyAuthorization(); //APIキー認証を行う
	$liveData = $handleYoutubeAPI->GetLiveInformation(); //ライブ情報を取得する
	if ($liveData != 0) {	
	    print("データベースの更新を行います\n");
	    HandleYoutubeDB::InsertDB($liveData);//取得したライブ情報をデータベースに格納する
	} else {
	    print("例外が発生したのでデータベースの更新は行われませんでした\n");
	}
    }
}
