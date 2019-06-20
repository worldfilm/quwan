<?php

namespace App\Service\Help;

use App\Model\Advert;
use App\Model\Mongo\Advert as MongoAdvert;

use Exception;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

/**
 *   视频基础服务
 *   @auther morgan
 */
class AdvertService
{

    public function __construct(){}

    /**
     * 获取广告
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function getAdverts( $request )
    {
        $adverts = MongoAdvert::getList( $request );
        if(!$adverts){
            $adverts = self::masterAdverts( $request );
        }

        return $adverts;
    }

    /**
     * 获取广告
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function getNewAdverts( $request )
    {
        $vip = $request->user() ? $request->user()->getUserVip($request->user()) :0;
        $cate_code = $request->cate_code;
        $cate_arr  = explode('_',$request->cate_code);
        if(count($cate_arr) > 1){
            $adverts = [];
            foreach($cate_arr as $v){
                $adverts[$v] = self::getCodeAdvert( $request, $v, $vip );
            }
        }else{
            $adverts = self::getCodeAdvert( $request, $cate_code, $vip );
        }
        return $adverts;
    }

    /**
     * [获取对应的code广告数据]
     * @param  [type] $request [description]
     * @return [type]          [description]
     */
    private static function getCodeAdvert( $request, $cate_code, $vip )
    {
        Redis::select(10);
        $redis_key = 'user_advert_list';
        $hash_key  = 'code_vip_' . $cate_code . '_' . $vip;
        if(Redis::hExists($redis_key,$vip)){
            $advert = Redis::hGet($redis_key,$hash_key);
            $advert = json_decode($advert,true);
        }else{
            $advert = Advert::getNewList( $request, $vip, $cate_code);
            Redis::hSet($redis_key,$hash_key,json_encode($advert));
        }
        return $advert;
    }

    /**
     * [masterAdverts 从mysql获取数据]
     * @param  [type] $request [description]
     * @return [type]          [description]
     */
    private static function masterAdverts( $request )
    {
        $adverts = Advert::getList( $request );

        if($adverts){
            self::syncAdvertData();
        }
        return $adverts;
    }


    /**
     * 使用命令行调用命令，从mysql刷数据至mongo
     * @return [type] [description]
     */
    private static function syncAdvertData(){
        if(Redis::setNx('cacheAdvertMongo',1)){
            //缓存advert资源至mongo
            Artisan::call('sync:extendedData');
            Redis::del('cacheAdvertMongo');
        }
    }


}