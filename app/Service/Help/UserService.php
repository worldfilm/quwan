<?php

namespace App\Service\Help;

use Exception;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Model\Video;
use App\Model\User;
use App\Model\Banner;
use App\Model\UserCollects;
use App\Model\VideoAssessLogs;
/**
 *   视频基础服务
 *   @auther morgan
 */
class UserService
{
	/**
	 * 用户收藏缓存键
	 * @var string
	 */
	const userCollectCacheKey = 'user_collect_cache';

    /**
     * 用户评价缓存键
     * @var string
     */
    const userAssessCacheKey = 'user_assess_cache';

	/**
	 * 主用户表缓存键
	 * @var string
	 */
    const ucUserCacheKey   = 'ucuser_cache';

    /**
     * 用户设备缓存键
     * @var string
     */
    const userIdentifierCacheKey = 'user_identifier_cache';


    const videoCollectTimes = 'video_collect_times';


    public function __construct(){}

    /**
     * 创建收藏
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function createCollect($userId, $videoId)
    {
        $state = UserCollects::create(['user_id'=>$userId,'video_id'=>$videoId]);
        if($state){
            if($redis = Redis::ping()){
                Redis::select(7);
                Redis::sADD(self::userCollectCacheKey.$userId,$videoId);
                Redis::select(0);
                Redis::hIncrBy(self::videoCollectTimes,$videoId,1);
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 取消收藏
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function  delCollect($userId, $videoId)
    {
        if($redis = Redis::ping()){
            Redis::select(7);
            Redis::sRem(self::userCollectCacheKey.$userId,$videoId);
            Redis::select(0);
            Redis::hIncrBy(self::videoCollectTimes,$videoId,-1);
        }
        $state = UserCollects::del($userId,$videoId);
        if($state){
            return  true;
        }else{
            return false;
        }
    }

    /**
     * 判断用户是否收藏
     * @return [type] [description]
     */
    public static function isUserCollect( $user, $videoId )
    {
        //如果是游客直接返回false
        if(!$user){
            return false;
        }
        $userId = $user ? $user->id : 0;

    	if($redis = Redis::ping()){
	    	Redis::select(7);
            $collect = Redis::sIsMember(self::userCollectCacheKey.$userId,$videoId);
	    }

	    if(!$collect){
    		$collect = self::cacheSetCollect($userId, $videoId);
    	}

        return $collect;
    }

    /**
     * 缓存用户收藏关联信息
     * @return [type] [description]
     */
    private static function cacheSetCollect($userId, $videoId)
    {
        $collect = UserCollects::getCollect($userId, $videoId);

        if( $collect->isEmpty() ){
            return false;
        }

        if($redis = Redis::ping()){
            Redis::select(7);
            Redis::sADD(self::userCollectCacheKey.$userId,$videoId);
        }

        return true;
    }



}