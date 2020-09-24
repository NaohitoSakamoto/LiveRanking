<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        return view('index');
    }

    public function GetLiveInformationFromDB(Request $request){
        $pageNumber = $request->input('pageNumber'); 
        $liveData = DB::table('youtube_informations')->offset(25 * $pageNumber)->limit(25)->get();
	return $liveData;
    }
}
