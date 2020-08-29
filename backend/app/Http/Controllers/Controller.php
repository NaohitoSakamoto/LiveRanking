<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\HandleYoutubeAPI;
use App\HandleYoutubeDB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        $liveData = array();

        $handleYoutubeAPI = new HandleYoutubeAPI();
        $handleYoutubeAPI->APIKeyAuthorization(); //APIキー認証を行う
        $liveData = $handleYoutubeAPI->GetLiveInformation(); //ライブ情報を取得する
        HandleYoutubeDB::InsertDB($liveData);//データベースを更新する
        return view('index', compact('liveData'));
    }
}
