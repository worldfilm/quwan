<?php

namespace App\Model\Mongo;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Service\Help\UserService;
use DB;

class Video extends Model
{
	protected $connection = 'mongodb';
	protected $collection = 'videos';
	protected $primaryKey = '_id';    //设置id
	protected $fillable = [
        'id','title','thumb_img_url','video_url','video_preview_url','category_id','likes','dislikes','rank','status','view_times','collect_times','description','contents','tags','play_times'
    ];

    /**
     * 免费视频分类ID
     */
    const FREE_VIDEO_CATEGORY_ID = 1;

    /**
     * 获取免费视频
     * @param  integer $page       [description]
     * @param  integer $pageSize   [description]
     * @return [type]              [description]
     */
    public static function getFreeVidoes( $page = 1, $pageSize = 10 )
    {
        $ret = [
                'page' => [],
                'list' => [],
            ];

        try{
            $pageSize = 10;
            $queryObj = self::query();
            $queryObj->where('category_id', self::FREE_VIDEO_CATEGORY_ID)->where('status', 0);

            $total = $queryObj->count();
            $data  = $queryObj
                    ->orderBy('rank', 'ASC')
                    ->orderBy('created_at', 'DESC')
                    ->skip(($page - 1) * $pageSize)
                    ->take($pageSize)
                    ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times']);

            $data = $data->toArray();
            foreach( $data as $key => $video ) {
                $data[$key] = [
                    'id'            => hashid($video['id'], 'video'),
                    'title'         => $video['title'],
                    'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                    'thumb_img_url' => $video['thumb_img_url'],
                    'views'         => $video['view_times'],
                ];
            }
            $page = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];

            $ret = [
                'page' => $page,
                'list' => $data,
            ];

            return $ret;
        }
        catch(\Exception $e){
            return $ret;
        }
    }

    public static function getRecommend(){
        try{
            $data = self::query()->where('status', 0)
                ->orderBy('real_view_times', 'desc')
                ->take(1000)
                ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times']);
            $data = $data->toArray();
            shuffle($data);
            $data = array_slice($data,996);
            foreach( $data as $key => $video ) {
                $data[$key] = [
                    'id'            => hashid($video['id'], 'video'),
                    'title'         => $video['title'],
                    'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                    'thumb_img_url' => $video['thumb_img_url'],
                    'views'         => $video['view_times'],
                ];
            }
            return $data;
        } catch(\Exception $e) {
            return [];
        }
    }

    public static function getFavorite( $page = 1, $pageSize = 10 ){
        $ret = [
            'page' => [],
            'list' => [],
        ];

        try{
            $queryObj = self::query()->where('status', 0);
            $total = $queryObj->count();
            $data  = $queryObj
                ->orderBy('view_times', 'desc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times']);

            $data = $data->toArray();
            foreach( $data as $key => $video ) {
                $data[$key] = [
                    'id'            => hashid($video['id'], 'video'),
                    'title'         => $video['title'],
                    'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                    'thumb_img_url' => $video['thumb_img_url'],
                    'views'         => $video['view_times'],
                ];
            }
            $page = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];

            $ret = [
                'page' => $page,
                'list' => $data,
            ];

            return $ret;
        } catch(\Exception $e) {
            return $ret;
        }
    }

    /**
     * 获取VIP视频列表
     * @param  [type]  $categoryId [分类ID]
     * @param  [type]  $tagsId     [标签ID]
     * @param  string  $sort       [排序]
     * @param  integer $pageSize   [分页数量]
     * @return [array]
     */
    public static function getVipVideos( $categoryId, $tag, $sort = 'new', $page, $pageSize = 20 )
    {
        $ret = [
                'page' => [],
                'list' => [],
            ];
        try{
//            $pageSize = 10;
            //获取排序字段 默认最新
            $ascOrDesc = 'DESC';

            switch( $sort ) {
                case 'score':
                    $orderBy = 'likes';
                    break;
                case 'views':
                    $orderBy = 'view_times';
                    break;
                case 'collect':
                    $orderBy = 'collect_times';
                    break;
                case 'new':
                    $orderBy = 'created_at';
                    break;
                default:
                    $orderBy = 'rank';
                    $ascOrDesc = 'ASC';
                    break;
            }

            $queryObj = self::query();
            // $where['categoryId'] = $categoryId;
            $where['tag']       = $tag;
            $queryObj->where('status', 0)->where('category_id', $categoryId);
            $queryObj->where(function($queryObj) use($where){
                if( $where['tag'] ){
                    $tags = array_filter(explode(',', $where['tag']));
                    foreach ($tags as $tag) {
                        $queryObj->orWhere('tags', 'like','%'.$tag.'%');
                    }
                }

            });

            $total = $queryObj->count();

            //评分排序 需计算评分值后再进行排序
            if($orderBy =='likes'){
                $queryObj->orderByRaw('(likes/(likes+dislikes)) DESC');
            }

            $data  = $queryObj
                    ->orderBy($orderBy, $ascOrDesc)
                    ->orderBy('id','DESC')
                    ->skip(($page - 1) * $pageSize)
                    ->take($pageSize)
                    ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times','tags','uid','price']);

            $videos = $data->toArray();

            $list   = [];
            foreach( $videos as $key => $video ) {
                $list[$key] = [
                    'id'            => hashid($video['id'], 'video'),
                    'title'         => $video['title'],
                    'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                    'thumb_img_url' => $video['thumb_img_url'],
                    'views'         => $video['view_times'],
                ];

                if($video['uid'] > 0 && $video['price'] > 0){
                    $userInfo = DB::table('users')->join('uc_users','users.uc_id','uc_users.id')->select('uc_users.username')->where('users.id',$video['uid'])->first();
                    $list[$key]['username'] = $userInfo ? $userInfo->username : "null";
                    $list[$key]['price'] = $video['price'];
                }
            }

            $page = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];

            $ret = [
                'page' => $page,
                'list' => $list,
            ];

            return $ret;
        }catch(\Exception $e){
            return $ret;
        }
    }

    /**
     * 获取搜索视频列表
     * @param  [type]  $content
     * @param  string  $sort       [排序]
     * @param  integer $pageSize   [分页数量]
     * @return [array]
     */
    public static function getSearchVideos( $content, $page, $pageSize = 20 )
    {
        $ret = [
            'page' => [],
            'list' => [],
        ];
        try{
            $queryObj = self::query();
            $queryObj->where('title', 'like','%'.$content.'%')->orWhere('tags', 'like','%'.$content.'%');
            $total = $queryObj->count();

            $data  = $queryObj
                ->orderBy('updated_at','DESC')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get(['id', 'title', 'thumb_img_url', 'updated_at','tags']);

            $videos = $data->toArray();

            $list   = [];
            foreach( $videos as $key => $video ) {
                $list[$key] = [
                    'id'            => hashid($video['id'], 'video'),
                    'title'         => $video['title'],
                    'thumb_img_url' => $video['thumb_img_url'],
                    'updated_at'    => $video['updated_at'] ,
                ];
            }

            $page = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];

            $ret = [
                'page' => $page,
                'list' => $list,
            ];

            return $ret;
        }catch(\Exception $e){
            return $ret;
        }
    }

    /**
     * 获取播放地址
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function getPlayUrl( $id )
    {
        $isFree = false;

        $video = self::where('id', $id)->first();

        $play_url = $video->video_url;
        //判断是否是免费视频
        if($video->category_id == self::FREE_VIDEO_CATEGORY_ID){
            $isFree = true;
            $play_url = $video->video_preview_url;
        }
        //增加播放次数
        $video->play_times = $video->play_times+1;
        $video->save();

        return ['id' => hashid($id, 'video'), 'play_url' => $play_url,'isFree'=>$isFree];
    }


    /**
     * [getRecommends 获取推荐视频]
     * @param  [type] $videoId  [description]
     * @param  [type] $page     [description]
     * @param  [type] $pageSize [description]
     * @return [type]           [description]
     */
    public static function getRecommends( $videoId, $user, $page, $pageSize )
    {
        $video = self::where('id', $videoId)->first();
        $tags = (isset($video->tags) && $video->tags) ? explode(',', $video->tags) : '';
        $queryObj = self::query();
        $data = [];
        if(is_array($tags)){
            $tag = array_pop($tags);
            $queryObj = $queryObj->where('tags','like','%'.$tag.'%');
            $total = $queryObj->count();
            $data = $queryObj
                    ->skip(($page - 1) * $pageSize)
                    ->take($pageSize)
                    ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times','collect_times']);
        }

        if(!$data){
            $total = $queryObj->count();
            $data = $queryObj->orderBy('id','DESC')
                    ->skip(($page - 1) * $pageSize)
                    ->take($pageSize)
                    ->get(['id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times','collect_times']);
        }

        $videos = $data->toArray();

        $list   = [];
        foreach( $videos as $key => $video ) {
            if($video['id'] == $videoId){
                unset($videos[$key]);
                continue;
            }
            $video['likes'] = isset($video['likes']) ? $video['likes'] : 1;
            $video['dislikes'] = isset($video['dislikes']) ? $video['dislikes'] : 1;
            $list[] = [
                'id'            => hashid($video['id'], 'video'),
                'title'         => $video['title'],
                'is_collect'    => UserService::isUserCollect($user,$video['id']),
                'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                'thumb_img_url' => $video['thumb_img_url'],
                'views'         => isset($video['view_times']) ? $video['view_times'] : 0,
                'collects'      => isset($video['collect_times']) ? $video['collect_times'] : 0
            ];
        }

        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];

        $ret = [
            'page' => $page,
            'list' => $list,
        ];

        return $ret;
    }

}