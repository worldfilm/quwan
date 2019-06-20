<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Service\Help\UserService;
use DB;

class Video extends Model {

    /**
     * 免费视频分类ID
     */
    const FREE_VIDEO_CATEGORY_ID = 1;

    protected $fillable = [
        'likes','dislikes','view_times','collect_times','tags','play_times'
    ];

    /**
     * 获取免费视频
     * @param  integer $page       [description]
     * @param  integer $pageSize   [description]
     * @return [type]              [description]
     */
    public static function getFreeVidoes( $page = 1, $pageSize = 10 )
    {
        $pageSize = 10;
        $queryObj = self::query();
        $queryObj->where('category_id', self::FREE_VIDEO_CATEGORY_ID)->where('status', 0);

        $total = $queryObj->count();
        $data  = $queryObj
                ->orderBy('rank', 'asc')
                ->orderBy('created_at', 'desc')
                ->select('id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

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
//        $pageSize = 10;
        //获取排序字段 默认最新
        $ascOrDesc = 'desc';
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
                $ascOrDesc = 'asc';
                break;
        }

        $queryObj = self::query();

        $queryObj->where('status', 0)->where('category_id', $categoryId);

        if( $tag ){
            $tags = explode(',', $tag);
            if($tags){
                $queryObj->where(function($queryObj) use($tags){
                    foreach ($tags as $tag) {
                        $queryObj->orWhere('tags', 'like','%'.$tag.'%');
                    }
                });
            }
        }


        $total = $queryObj->count();

        //评分排序 需计算评分值后再进行排序
        if($orderBy =='likes'){
            $queryObj->orderByRaw('(likes/(likes+dislikes)) desc');
        }

        $data  = $queryObj
                ->orderBy($orderBy, $ascOrDesc)
                ->orderBy('id','desc')
                ->select('id', 'title', 'thumb_img_url', 'likes', 'dislikes', 'view_times','tags','uid','price')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

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
     * 获取视频详情
     * @param  [type]  $id    [description]
     * @param  boolean $isVip [description]
     * @return [type]         [description]
     */
    public static function detail( $id ,$user)
    {
        $data = self::with('collect')->with('assess')->where('id', $id)->first();

        if( !empty($data) ) {

            $assessLog   = 0;
            $collect     = false;
            // if($user){
            //     $collect     = $data->collect->where('user_id',$user->id)->toArray() ? true : false;
            //     $assessLog   =  $data->assess->where('user_id',$user->id)->first();
            // }

            $data     = $data->toArray();

            $category = Category::where('id', $data['category_id'])->first();
            $ret      = [
                'id'            => hashid($data['id'], 'video'),
                'title'         => $data['title'],
                'tags'          => $data['tags'] ? explode(',', $data['tags']) : [],
                'views'         => $data['view_times']+1,
                'like'          => $data['likes'] ? ceil(($data['likes'] / ($data['likes'] + $data['dislikes']) * 100)) : 100,
                // 'is_collect'    => $collect,
                // 'assess'        => $assessLog ? $assessLog->assess_type :0,
                'description'   => $data['description'],
                'content'       => $data['contents'] ? array_values(json_decode($data['contents'], true)) : [],
                'thumb_img_url' => $data['thumb_img_url'],
                'play_times'    => $data['play_times'],
                'created_at'    => strtotime($data['created_at']),
                'real_view_times' => $data['real_view_times']

            ];
            if( !empty($category) ) {
                $ret['category'] = [
                    'id'   => hashid($category->id, 'category'),
                    'name' => $category->name,
                ];
            }

            //更新观看次数
            $addNum = rand(2,5);
            self::where('id', $id)->update(['view_times'=>$data['view_times']+$addNum,'real_view_times'=>$data['real_view_times']+1]);
            return $ret;
        }
        else {
            return false;
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

        if($video){
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
            $list[] = [
                'id'            => hashid($video['id'], 'video'),
                'title'         => $video['title'],
                'is_collect'    => UserService::isUserCollect($user,$video['id']),
                'like'          => $video['likes'] ? ceil(($video['likes'] / ($video['likes'] + $video['dislikes']) * 100)) : 100,
                'thumb_img_url' => $video['thumb_img_url'],
                'views'         => $video['view_times'],
                'collects'      => $video['collect_times']
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

    // /**
    //  * 关联标签表
    //  * @return [type] [description]
    //  */
    // public function tags()
    // {
    //     return $this->hasMany('\App\Model\VideoTags', 'video_id', 'id');
    // }

    /**
     * 关联收藏表
     * @return [type] [description]
     */
    public function collect()
    {
    	return $this->hasMany('\App\Model\UserCollects', 'video_id', 'id');
    }


    /**
     * 关联视频评价表
     * @return [type] [description]
     */
    public function assess()
    {
        return $this->hasMany('\App\Model\VideoAssessLogs','video_id','id');
    }

}
