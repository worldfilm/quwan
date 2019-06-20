<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ConfigController extends BaseController {

    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->response = ['status' => 0, 'message' => 'OK', 'data' => []];
        $this->configRedisKey = 'qvod_config';
    }


    /**
     * 获取配置信息
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function getConfig( Request $request, $alias='' )
    {
        if( $alias ) {
            if( $config = Redis::hGet($this->configRedisKey,$alias) ){
                $data   = json_decode($config,true);
            }else{
                $data   = Config::getConfigByAlias(strtolower($alias));
                Redis::hSet($this->configRedisKey,$alias,json_encode($data));
            }
        }else{
            $data  = Config::getConfig();
        }

        if($data){
            return response()->json(['status' => 0, 'message' => 'OK', 'data' => $data]);
        }else{
            return response()->json(['status' => 401, 'message' => '未找到配置信息']);
        }

    }


}
