<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
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
        HandleYoutubeDB::InsertDB($liveData);//取得したライブ情報をデータベースに格納する
        return view('index');
    }

    public function GetLiveInformationFromDB(Request $request){
        $pageNumber = $request->input('pageNumber');
        
        $liveData = DB::table('youtube_informations')->offset(25 * $pageNumber)->limit(25)->get();
        return $liveData;
    }
}
