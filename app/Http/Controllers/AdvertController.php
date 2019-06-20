<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Advert;
use App\Service\Help\AdvertService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AdvertController  extends BaseController {

    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(){}


    /**
     * 获取广告
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function getAdverts( Request $request )
    {
        $list = AdvertService::getAdverts( $request );
        return response()->json(['status'  => 0,'message' => 'OK','data'=> $list]);

    }

    /**
     * 获取广告
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function getNewAdverts( Request $request )
    {
        $list = AdvertService::getNewAdverts( $request );
        return response()->json(['status'  => 0,'message' => 'OK','data'=> $list]);

    }
}
