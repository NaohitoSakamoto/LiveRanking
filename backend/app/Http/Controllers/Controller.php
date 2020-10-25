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

    /* APIを叩いたときに行われる処理 */
    public function GetLiveInformationFromDB(Request $request){
        $pageNumber = $request->input('pageNumber'); 
        $liveData = DB::table('youtube_informations')->orderBy('concurrentViewer', 'desc')->offset(25 * $pageNumber)->limit(25)->get();
	    return $liveData;
    }
}
