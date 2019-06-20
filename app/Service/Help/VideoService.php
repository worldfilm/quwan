<?php

namespace App\Service\Help;

use Exception;

use App\Model\Video;
use App\Model\User;
use App\Model\Banner;
use App\Model\VideoReview;
use App\Model\VideoAssessLogs;
use App\Model\Mongo\Video as MongoVideo;

use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

/**
 *   视频基础服务
 *   @auther morgan
 */
class VideoService
{
	/**
	 * 视频详情缓存键
	 * @var string
	 */
	const detailCacheKey = 'video_detail_cache';

	/**
	 * 视频播放地址缓存键
	 * @var string
	 */
    const playCacheKey   = 'video_play_url_cache';


    /**
     * banner图片缓存键
     * @var string
     */
    const bannerCacheKey = 'video_banners_cache';

    /**
     * 免费视频缓存键
     */
    const freeVideoCacheKey = 'video_free_video_cache';

    /**
     * 行为自增列表
     */
    const videoIncViews      = 'video_inc_views',
          videoIncPlayTimes  = 'video_inc_play_times',
          videoIncRealViews  = 'video_inc_real_views',
          videoIncLikeTimes  = 'video_inc_like_times',
          videoIncDislikeTimes = 'video_inc_dislike_times';

    /**
     *  用户缓存键
     */
    const userAssessCacheKey = 'user_assess_cache';

    const userEvaluationList = 'user_evaluation_list';

    public function __construct(){}

    /**
     * [getFreeVideo 获取免费视频 to mongo ]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数量]
     * @return [type]           [array]
     */
    public static  function  getFreeVideo($page, $pageSize)
    {
        $video =MongoVideo::getFreeVidoes($page,$pageSize);

        if(!$video['list']){
            $video = self::masterFreeVideo($page, $pageSize);
        }

        return $video;
    }

    public static function getRecommend(){
        $video =MongoVideo::getRecommend();
//        if(!$video['list']){
//            $video = self::masterRecommend();
//        }
        return $video;
    }

    public static function getFavorite($page, $pageSize){
        $video =MongoVideo::getFavorite($page, $pageSize);
//        if(!$video['list']){
//            $video = self::masterFavorite($page, $pageSize);
//        }
        return $video;
    }

    /**
     * [masterFreeVideo 获取免费视频 to mongo]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数量]
     * @return [type]           [array]
     */
    private  static  function masterFreeVideo($page, $pageSize)
    {
        //获取列表
        $video = Video::getFreeVidoes($page, $pageSize);
        self::syncVideoData();
        return $video;
    }


    /**
     * 获取vip视频列表
     * @param  [type] $category [分类ID]
     * @param  [type] $tag      [标签]
     * @param  [type] $sort     [排序]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数据量]
     * @return [type]           [array]
     */
    public static function getVipVideos($category, $tag, $sort, $page, $pageSize)
    {
        $video = MongoVideo::getVipVideos($category, $tag, $sort, $page, $pageSize);

        if(!$video['list']){
            $video = self::masterVipVideos($category, $tag, $sort, $page, $pageSize);
        }

        return $video;
    }

    /**
     * 获取搜索视频列表
     * @param  [type] $category [分类ID]
     * @param  [type] $tag      [标签]
     * @param  [type] $sort     [排序]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数据量]
     * @return [type]           [array]
     */
    public static function getSearchVideos($content, $page, $pageSize)
    {
        $video = MongoVideo::getSearchVideos($content, $page, $pageSize);

        if(!$video['list']){
            $video = Video::getSearchVideos($content, $page, $pageSize);

            if($video['list']){
                self::syncVideoData();
            }
        }

        return $video;
    }

    /**
     * mysql获取vip视频
     * @param  [type] $category [分类ID]
     * @param  [type] $tag      [标签]
     * @param  [type] $sort     [排序]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数据量]
     * @return [type]           [array]
     */
    private static function masterVipVideos($category, $tag, $sort, $page, $pageSize)
    {
        $video = Video::getVipVideos($category, $tag, $sort, $page, $pageSize);

        if($video['list']){
            self::syncVideoData();
        }
        return $video;
    }

    /**
     * 使用命令行调用命令，从mysql刷数据至mongo
     * @return [type] [description]
     */
    private static function syncVideoData(){
        if(Redis::setNx('rkcacheMongo',1)){
            Redis::expireAt('rkcacheMongo',time()+30);
            //缓存video资源至mongo
            Artisan::call('syncvideo:data');
            Redis::del('rkcacheMongo');
        }
        return true;
    }

    /**
     * 获取banner
     * @return [type] [description]
     */
    public static function getBanner()
    {
    	$banner = [];
    	if($redis = Redis::ping()){
	    	$banner = Redis::hgetAll(self::bannerCacheKey);//json_decode(Redis::hgetAll(self::bannerCacheKey),true);
            $newBanner = [];
	    	foreach ($banner as $key => $value) {
	    		$newBanner[] = json_decode($value,true);
	    	}
            $banner = $newBanner;
	    }

	    if(!$banner){
    		$banner = self::cacheBanner();
    	}

    	return $banner;
    }

    /**
     * 获取游戏banner
     * @return [type] [description]
     */
    public static function getGameBanner()
    {
        $banner = [];
        $game_banner_cache = 'game_banner_cache';
        if($banners = Redis::get($game_banner_cache)){
            $banners = json_decode($banners);
        }else{
            $banners = Banner::select('id','url','thumb_img_url','type')->where('position', 1)->orderBy('rank','asc')->orderBy('id','desc')->get();
            Redis::set($game_banner_cache,$banners);
        }

        return $banners;
    }

    /**
     * 缓存banner信息
     * @return [type] [description]
     */
    private static function cacheBanner()
    {
        $banner = Banner::getList('free');

        if($redis = Redis::ping()){
            foreach ($banner as  $value) {
                Redis::hset(self::bannerCacheKey,dehashid($value['id'], 'video'),json_encode($value));
            }
        }

        return $banner;
    }

    /**
     * 获取视频详情
     * @param  [type] $videoId [description]
     * @param  [type] $user    [description]
     * @return [type]          [description]
     */
    public static function getDetail( $videoId, $user )
    {
    	$video = [];
        if($redis = Redis::ping()){
            $video = json_decode(Redis::hget(self::detailCacheKey,$videoId),true);

            if(!$video){//缓存不存在  读取数据库 并放入缓存
	            $video = self::cacheDetail( $videoId, $user );
	        }else{
	            //存入缓存  并更新播放次数和查看次数
	        	Redis::hIncrBy(self::videoIncViews,$videoId,rand(2,5));
                Redis::hIncrBy(self::videoIncRealViews,$videoId,1);
	        }
        }else{
        	$video = self::cacheDetail( $videoId, $user );
        }

        unset($video['real_view_times']);
        unset($video['play_times']);
        $video['content'] = $video['content'] ? array_values($video['content']) : [];

        return $video;
    }

    /**
     * 缓存视频详情
     * @param  [type] $videoId [description]
     * @param  [type] $user    [description]
     * @return [type]          [description]
     */
    private static function cacheDetail( $videoId, $user )
    {
    	$video = Video::detail($videoId, $user);
    	if( $video && $redis = Redis::ping()){
            Redis::hset(self::detailCacheKey,$videoId,json_encode($video));
        }

        return $video;
    }


    /**
     * 获取播放地址
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function getPlayUrl( $videoId )
    {
    	if( $redis = Redis::ping() ){
            $video = json_decode( Redis::hget( self::playCacheKey, $videoId ), true);

            if(!$video){//缓存不存在  读取数据库 并放入缓存
                $video = self::cachePlayUrl( $videoId );
            }else{
                $detail = json_decode( Redis::hget( self::detailCacheKey, $videoId ),true);
                //增加播放次数
                Redis::hIncrBy( self::videoIncPlayTimes, $videoId, 1);
            }

        }else{
            $video = Video::getPlayUrl( $videoId );
        }

        return $video;
    }

    /**
     * 缓存播放地址
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    private static function cachePlayUrl( $videoId )
    {
    	$video = Video::getPlayUrl( $videoId );
    	if( $video && $redis = Redis::ping() ){
    		Redis::hset( self::playCacheKey, $videoId, json_encode($video) );
    	}
    	return $video;
    }



    /**
     * [addLike 好评次数]
     * @param [type] $videoId [description]
     */
    public static function addLike( $userId,$videoId )
    {
        Redis::select(0);
        Redis::hIncrBy(self::videoIncLikeTimes,$videoId,1);
        Redis::select(8);
        Redis::sADD(self::userEvaluationList.$userId,$videoId);
    }

    /**
     * [disLike 差评次数记录]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function disLike( $userId,$videoId )
    {
        Redis::select(0);
        Redis::hIncrBy(self::videoIncDislikeTimes,$videoId,1);
        Redis::select(8);
        Redis::sADD(self::userEvaluationList.$userId,$videoId);
    }

    /**
     * 判断是否已评价
     * @param  [type]  $userId  [description]
     * @param  [type]  $videoId [description]
     * @return boolean          [description]
     */
    public static function isEvaluation( $userId, $videoId )
    {
        $eval = false;

        if(!$userId){
            return false;
        }

        if($redis = Redis::ping()){
            Redis::select(8);
            $eval = Redis::sIsMember(self::userEvaluationList.$userId,$videoId);
        }

        if(!$eval){
            $eval = self::cacheEvaluation($userId, $videoId);
        }

        return $eval;
    }

    /**
     * 缓存已评价信息
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    private static function cacheEvaluation( $userId, $videoId )
    {
        $eval = VideoAssessLogs::getLogByItem( $userId, $videoId );

        if( !$eval ){
            return false;
        }

        if($redis = Redis::ping()){
            Redis::select(8);
            Redis::sADD(self::userEvaluationList.$userId,$videoId);
        }

        return true;
    }


    /**
     * 记录用户评价记录
     * @param array $option [description]
     */
    public static function setAssessLog($option = [])
    {
        $state = VideoAssessLogs::create($option);
        return $state;
    }

    /**
     * [getCommentsCount 获取评论条数]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function getReviewCount( $videoId )
    {
        return VideoReview::getCount( $videoId );
    }


    /**
     * [getCommentsCount 获取评论]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function getReview( $videoId, $page, $pageSize )
    {
        return VideoReview::getlist( $videoId, $page, $pageSize );
    }

    /**
     * [getRecommends 获取推荐视频 mongo]
     * @param  [type] $videoId  [description]
     * @param  [type] $page     [description]
     * @param  [type] $pageSize [description]
     * @return [type]           [description]
     */
    public static function getRecommends( $videoId, $user, $page, $pageSize )
    {
        $video =MongoVideo::getRecommends( $videoId, $user, $page, $pageSize );

        if(!$video['list']){
            $video = self::masterRecommends( $videoId, $user, $page, $pageSize );
        }

        return $video;
    }


    /**
     * [masterFreeVideo 获取推荐视频 mysql]
     * @param  [type] $page     [页数]
     * @param  [type] $pageSize [每页数量]
     * @return [type]           [array]
     */
    private  static  function masterRecommends( $videoId, $user, $page, $pageSize )
    {
        //获取列表
        $video = Video::getRecommends( $videoId, $user, $page, $pageSize );
        return $video;
    }

    /**
     * [storeReview 用户评论]
     * @param  [type] $videoId [description]
     * @param  [type] $userId  [description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public static function  storeReview( $videoId,$userId,$content)
    {
        $state = VideoReview::create(['video_id'=>$videoId,'user_id'=>$userId,'content'=>$content]);
        return $state;
    }

    /**
     * [lastReview 获取当前用户最后一条评论]
     * @param  [type] $videoId [description]
     * @param  [type] $userId  [description]
     * @return [type]          [description]
     */
    public static function lastReview( $videoId,$userId )
    {
        $lastreview = VideoReview::with('user')->where(['video_id'=>$videoId,'user_id'=>$userId])->orderBy('id','desc')->first()->toArray();
        $data = [
            'nickname'=>$lastreview['user']['nickname'],
            'content'=>$lastreview['content'],
            'creat_time'=>date('Y-m-d',strtotime($lastreview['created_at']))
        ];

        return $data;
    }

    /**
     * [getReviewTime 查看评论时间]
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public static function getReviewTime( $userId )
    {
        if(Redis::ping()){
            Redis::select(3);
            if(Redis::get('review_time'.$userId)){
                return true;
            }
        }
        return false;
    }

    /**
     * [setReviewTime 设置评论过期时间]
     * @param [type] $userId [description]
     */
    public static function setReviewTime( $userId )
    {
        if(Redis::ping()){
            Redis::select(3);
            Redis::set('review_time'.$userId,5);
            Redis::expireAt('review_time'.$userId,time()+5);
        }
    }

    //  /**
    //  * 判断用户是否评价
    //  * @return [type] [description]
    //  */
    // public static function isUserAssess( $user, $videoId )
    // {
    //     //如果是游客直接返回false
    //     if(!$user){
    //         return false;
    //     }
    //     $userId = $user->id;

    //     if($redis = Redis::ping()){
    //         $assess = Redis::hget(self::userAssessCacheKey,$userId.'_'.$videoId);
    //     }

    //     if(!$assess){
    //         $assess = self::cacheAssess($userId, $videoId);
    //     }

    //     return $assess ? true : false;
    // }

    // /**
    //  * 缓存用户评价关联信息
    //  * @return [type] [description]
    //  */
    // private static function cacheAssess($userId,$videoId)
    // {
    //     $collect = VideoAssessLogs::getLogByItem($userId,$videoId);

    //     if(!$collect){
    //         return false;
    //     }
    //     if($redis = Redis::ping()){
    //         Redis::hset(self::userAssessCacheKey,$userId.'_'.$videoId,1);
    //     }

    //     return true;
    // }
}