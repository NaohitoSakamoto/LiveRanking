<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\HandleYoutubeAPI;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        $handleYoutubeAPI = new HandleYoutubeAPI();
        $handleYoutubeAPI->APIKeyAuthorization(); //APIキー認証を行う
        $handleYoutubeAPI->GetLiveInformation(); //ライブ情報を取得する
        return view('index');
    }
}
