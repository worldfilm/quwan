<?php

namespace App\Http\Controllers;

use App\Model\Banner;
use App\Model\News;
use App\Model\UserVip;
use function GuzzleHttp\Psr7\str;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Config;
use App\Model\Video;
use App\Model\User;
use App\Model\UserCollects;
use App\Model\VipCategory;
use App\Model\VideoAssessLogs;
use App\Model\Tag;
use App\Service\Help\VideoService;
use App\Service\Help\UserService;
use Illuminate\Support\Facades\Redis;
use App\Model\AdvertPc;
use DB;
use App\Model\Category;
use Log;
use App\Model\Actor;

class VideoController extends BaseController {

    private $configRedisKey = '';
    public function __construct()
    {
        $this->configRedisKey = 'qvod_config';

    }

    public function bannerList(){
        $data['banner'] =  VideoService::getBanner();
        $data['news'] =  News::getNews();
        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $data]);
    }

    /**
     * 获取免费列表
     * @param Request $request
     * @return type
     */
    public function getFreeList( Request $request )
    {
        $page     = intval($request->input('page', 1));
        $pageSize = intval($request->input('page_size', 20));
        $data     = VideoService::getFreeVideo($page, $pageSize);
        //\Log::info('免费视频数据-------' . $data);
        if( $page == 1 ) {
            $data['banner'] =  VideoService::getBanner();
        }
        if(count($data['list']) >= 1){
            $vip = $request->user() ? $request->user()->getUserVip($request->user()) :0;
            //免费视频列表，每隔1条插入一条广告
            $lists = $data['list'];
            $std = new AdvertPc();
            $adverts = $std->getList($request, $vip, 'AppVideoFreeList');
            $advert_count = count($adverts);
            if($advert_count > 0){
                $statis = 0;
                foreach($lists as $k => $v){
                    foreach($adverts as $key => $val){
                        if(($k + 1) == $val['rank']){
                            $advert = $adverts[$key];
                            $advert['is_advert'] = 1;
                            array_splice($lists,$statis,0,[$advert]);
                        }
                    }
                    $statis += 2;
                }
                $data['list'] = $lists;
            }
        }

        $response = [
            'status'  => 0,
            'message' => 'OK',
            'data'    => $data
        ];

        return response()->json($response);
    }

    /**
     * 获取今日精选
     */
    public function recommend(){
        $data = VideoService::getRecommend();
        $response = [
            'status'  => 0,
            'message' => 'OK',
            'data'    => $data
        ];

        return response()->json($response);
    }


    public function favorite( Request $request ){
        $page     = intval($request->input('page', 1));
        $pageSize = intval($request->input('page_size', 20));
        $data = VideoService::getFavorite($page, $pageSize);
        $response = [
            'status'  => 0,
            'message' => 'OK',
            'data'    => $data
        ];

        return response()->json($response);
    }

    /**
     * 获取VIP区视频列表
     * @param  Request $request
     * @param  string  $category [description]
     * @param  string  $tag      [description]
     * @return [type]            [description]
     */
    public function getVipList( Request $request )
    {
        $category   = dehashid(get_param($request, 'category_id'), 'category');
        $tag        = get_param($request, 'tag');
        $sort       = get_param($request, 'sort');
        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',10));


        $videos = VideoService::getVipVideos($category, $tag, $sort, $page, $pageSize);

//        $vip = $request->user() ? $request->user()->getUserVip($request->user()) :0;
        //vip列表，每隔5条插入一条广告
        $lists = $videos['list'];
//        $std = new AdvertPc();
        //category_id 18为巷友
//        $adverts = $category == 18 ? $std->getList($request, $vip, 'AppPictureList') : $std->getList($request, $vip, 'AppVideoList');
//        $advert_count = count($adverts);
//        if($advert_count > 0){
//            $statis = 0;
//            foreach($lists as $k => $v) {
//                if($k == 0 && $page == 1) {
//                    foreach ($adverts as $key => $val) {
//                        if($k + 1 == $val['rank']){
//                            $advert = $adverts[$key];
//                            $advert['is_advert'] = 1;
//                            array_splice($lists,$k,0,[$advert]);
//                            $statis ++;
//                            break;
//                        }
//                    }
//                }elseif(($k + 1) % 5 == 0){
//                    $advert_key = ($k + 1 + 5 + ($pageSize * ($page - 1)) ) / 5;
//                    foreach($adverts as $key => $val){
//                        if($advert_key == $val['rank']){
//                            $advert = $adverts[$key];
//                            $advert['is_advert'] = 1;
//                            array_splice($lists,$k + $statis + 1,0,[$advert]);
//                            $statis ++;
//                            break;
//                        }
//                    }
//                }
//            }
            $videos['list'] = $lists;
//        }

        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $videos]);
    }

    /**
     * 视频搜索
     */
    public function getSearchList( Request $request ){
        $content = get_param($request, 'content');
        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',10));
        $ip = $request->getClientIp();
        if(!$content){
            return response()->json(['status'=>404,'message'=>'搜索内容不能为空']);
        }

        //次数限制
/*        Redis::select(7);
        $redis_key = 'user_search:' . $ip;
        if(Redis::exists($redis_key)){
            return response()->json(['status'=>405,'message'=>'请求次数过多，请稍后再试']);
        }
        Redis::set($redis_key,1);
        Redis::expire($redis_key,5);*/

        //获取视频
        $videos = VideoService::getSearchVideos($content, $page, $pageSize);

        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $videos]);
    }

    /**
     * 获取热门搜索
     */
    public function getHotSearchList( Request $request ){
        Redis::select(8);
        $redis_key = 'hot_search_video';
        $video_hot_ids = Redis::zRevRange($redis_key, 0,7);
        $videos = Video::whereIn('id', $video_hot_ids)->where('status',0)->get(['id', 'title', 'thumb_img_url']);
        $list   = [];
        foreach( $videos as $key => $video ) {
            $list[$key] = [
                'id'            => hashid($video['id'], 'video'),
                'title'         => $video['title'],
                'thumb_img_url' => $video['thumb_img_url'],
            ];
        }
        return response()->json(['status' => 0, 'message' => 'OK', 'data' => ['list' => $list]]);
    }

    /**
     * 获取详情
     * @param Request $request
     * @param type $videoId
     * @return type
     */
    public function getDetail( Request $request, $videoId = '' )
    {
        if( !empty($videoId) && $videoId = dehashid($videoId, 'video') ) {
            $user = $request->user();
            $video = VideoService::getDetail($videoId,$user);
            $user_id = $user ? $user->id: 0;

            if( !empty($video) ) {
                    $video['is_collect'] = UserService::isUserCollect($user,$videoId);
                    $video['assess']     = VideoService::isEvaluation($user_id,$videoId) ? 1 : 0;
                    $video['comments_count'] = VideoService::getReviewCount($videoId);
                    $response = [
                        'status'  => 0,
                        'message' => 'OK',
                        'data'    => $video,
                    ];
            }
            else {
                $response = [
                    'status'  => 404,
                    'message' => 'video not exist'
                ];
            }
        }
        else {
            $response = [
                'status'  => 404,
                'message' => 'video not exist'
            ];
        }
        return response()->json($response);
    }

    /**
     * 获取视频播放地址
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getPlay( Request $request, $videoId = '' )
    {
        $configRedisKey = 'qvod_config';
        $is_search = intval($request->input('is_search',0));

        if( !$videoId || !$videoId = dehashid($videoId, 'video') ) {
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }
        //若是搜索进入，则记录
        if($is_search){
            $redis_key = 'hot_search_video';
            Redis::select(8);
            Redis::zIncrBy($redis_key, 1,$videoId);
        }

        $video = VideoService::getPlayUrl( $videoId );

        //获取配置
        $vip_config_key = 'vip_all_free';
        if( $is_free = Redis::hGet ($configRedisKey,$vip_config_key)){
            $is_free = json_decode($is_free,true);
            $is_free = $is_free['description'];
        }else{
            $config = Config::getConfigByAlias($vip_config_key);
            $is_free = $config ? json_encode($config) : null;
            Redis::hSet($configRedisKey,$vip_config_key,$is_free);
        }


        //免费视频直接返回播放地址
//        if( $video['isFree'] || $is_free ){
//            return response()->json(['status' => 0, 'message' => 'OK', 'data' => $video]);
//        }

//        if( !$user = $request->user() ){
//            return response()->json(['status' => 401, 'message' => '请先登录']);
//        }

//        if( !$user->isVip($user) ) {
//            return response()->json(['status' => 403, 'message' => '需要会员']);
//        }

        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $video]);
    }



    /**
     * 好评
     * @param Request $request [description]
     * @param string  $videoId [description]
     */
    public function addLike( Request $request, $videoId = '' )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if( !$videoId ||  !$video_id = dehashid($videoId,'video')) {
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }

        if( !$video = Video::find($video_id)){
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }

        if( $video->category_id != 1 && !$user->isVip($user)){
            return response()->json(['status' => 403, 'message' => '需要会员']);
        }

        if( VideoService::isEvaluation($user->id,$video_id) ){
            return response()->json(['status' => 405, 'message' => '视频仅可评价一次']);
        }

        VideoService::addLike( $user->id, $video_id );
        $state = VideoService::setAssessLog(['user_id'=>$user->id,'video_id'=>$video_id,'assess_type'=>1]);

        if( $state ) {
            return response()->json(['status' => 0, 'message' => '操作成功','data'=>['like'=>ceil(($video->likes / ($video->likes + $video->dislikes) * 100))]]);
        }
        else{
            return response()->json(['status' => 405, 'message' => '操作失败']);
        }
    }

    /**
     * 差评
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function disLike( Request $request, $videoId = '' )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if( !$videoId ||  !$video_id = dehashid($videoId,'video')) {
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }

        if( !$video = Video::find($video_id)){
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }

        if( VideoService::isEvaluation($user->id,$video_id) ){
            return response()->json(['status' => 405, 'message' => '视频仅可评价一次']);
        }

        if( $video->category_id != 1 && !$user->isVip($user)){
            return response()->json(['status' => 403, 'message' => '需要会员']);
        }

        //更新视频信息
        VideoService::disLike( $user->id, $video_id );
        $state = VideoService::setAssessLog(['user_id'=>$user->id,'video_id'=>$video_id,'assess_type'=>2]);

        if( $state ) {
            return response()->json(['status' => 0, 'message' => '操作成功','data'=>['like'=>ceil(($video->likes / ($video->likes + $video->dislikes) * 100))]]);
        }
        else{
            return response()->json(['status' => 405, 'message' => '操作失败']);
        }
    }


    /**
     * 创建收藏
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function createCollect( Request $request, $videoId = '')
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if( !$videoId ||  !$video_id = dehashid($videoId,'video') ) {
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }

        if( UserService::isUserCollect($user, $video_id) ) {
            return response()->json(['status' => 404, 'message' => '视频已收藏']);
        }

        $state =  UserService::createCollect($user->id,$video_id);// UserCollects::firstOrCreate(['user_id'=>$user->id,'video_id'=>$video_id]);

        if( $state ) {
            return response()->json(['status' => 0, 'message' => '操作成功']);
        }
        else{
            return response()->json(['status' => 405, 'message' => '收藏失败']);
        }
    }

    public function getCollectCount( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $total = DB::table('video_upload')->where('uid',$user->id)->count();

        $sql = "SELECT uid,COUNT(*) as total FROM `qvod_video_upload` WHERE uid != '' GROUP BY uid;";
        $uploadCounts = DB::select($sql);
        $few = 0;
        $more = 0;
        if($uploadCounts){
            foreach ($uploadCounts as $uploadCount){
                if($uploadCount->total <= $total){
                    $few++;
                } else {
                    $more++;
                }
            }
        }
        if($few + $more > 0){
            $surpass = ceil($few/($few + $more) * 100)."%";
        } else {
            $surpass = 0.00;
        }

        $collectCount = UserCollects::query()->where('user_id',$user->id)->count();

        $data = [
            'uploadVideo'   => $total,
            'surpass'       => $surpass,
            'collectVideo'  => $collectCount
        ];

        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $data]);
    }

    /**
     * 取消收藏
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function cancelCollect(Request $request, $videoId = '')
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $videoIds = explode(',',$videoId);
        $videoIds = array_filter($videoIds);
        foreach ($videoIds as $key=>$value){
            if( !$value || !$video_id = dehashid($value,'video') ) {
                return response()->json(['status' => 404, 'message' => '视频不存在']);
            }
            $state = UserService::delCollect($user->id,$video_id);
            if( !$state ){
                return response()->json(['status' => 405, 'message' => '取消收藏失败']);
            }
        }

        return response()->json(['status' => 0, 'message' => '操作成功']);
    }

    /**
     * [getComments 获取评论]
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function getReviews( Request $request, $videoId = '' )
    {
        if( !$videoId ||  !$video_id = dehashid($videoId,'video') ) {
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }
        $page     = intval($request->input('page', 1));
        $pageSize = intval($request->input('page_size', 10));

        $list = VideoService::getReview($video_id,$page,$pageSize);

        return response()->json(['status' => 0, 'message' => 'ok','data'=>$list ]);
    }

    /**
     * [getRecommends 获取推荐视频]
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function getRecommends( Request $request, $videoId = '' )
    {
        if( !$videoId ||  !$video_id = dehashid($videoId,'video') ) {
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }
        $page     = intval($request->input('page', 1));
        $pageSize = intval($request->input('page_size', 10));
        $user  = $request->user();

        $list = VideoService::getRecommends($video_id, $user, $page, $pageSize);

        if(count($list['list']) >= 5){
            $vip = $request->user() ? $request->user()->getUserVip($request->user()) :0;
            //vip列表，每隔10条插入一条广告
            $lists = $list['list'];
            $std = new AdvertPc();
            $adverts = $std->getList($request, $vip, 'AppRecommendList');//获取所有广告
            $advert_count = count($adverts);
            if($advert_count > 0){
                $statis = 0;
                foreach($lists as $k => $v) {
                    if($k == 0 && $page == 1) {
                        foreach ($adverts as $key => $val) {
                            if ($k + 1 == $val['rank']) {
                                $advert = $adverts[$key];
                                $advert['is_advert'] = 1;
                                array_splice($lists,$k,0,[$advert]);
                                $statis ++;
                                break;
                            }
                        }
                    }elseif(($k + 1) % 5 == 0){
                        $advert_key = ($k + 1 + 5 + ($pageSize * ($page - 1)) ) / 5;
                        foreach($adverts as $key => $val){
                            if($advert_key == $val['rank']){
                                $advert = $adverts[$key];
                                $advert['is_advert'] = 1;
                                array_splice($lists,$k + $statis + 1,0,[$advert]);
                                $statis ++;
                                break;
                            }
                        }
                    }
                }
                $list['list'] = $lists;
            }
        }
        return response()->json(['status' => 0, 'message' => 'ok','data'=>$list ]);
    }

    /**
     * TODO  作废功能 代码清除
     * [postReviews 用户评论]
     * @param  Request $request [description]
     * @param  string  $videoId [description]
     * @return [type]           [description]
     */
    public function postReviews( Request $request, $videoId = '' )
    {
        return response()->json(['status' => 405, 'message' => '系统维护，暂停评论功能']);

        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if( !$videoId || !$video_id = dehashid($videoId,'video') ) {
            return response()->json(['status' => 404, 'message' => '视频不存在']);
        }
        //5秒评论时间
        if( VideoService::getReviewTime($user->id) ){
            return response()->json(['status' => 405, 'message' => '评论太频繁，请稍后再试']);
        }

        $content = get_param($request, 'content');

        if(!$content){
            return response()->json(['status' => 405, 'message' => '评论不能为空']);
        }

        if(mb_strlen($content,'utf8') > 20){
            return response()->json(['status' => 405, 'message' => '最多20字评论']);
        }

        if(mb_strlen($content,'utf8') < 4){
            return response()->json(['status' => 405, 'message' => '最少4字评论']);
        }

        if($keywordsRule = UserController::getConfig('keyword_masking')){
            foreach ($keywordsRule['content'] as $key => $value) {
                if(strpos($content,$value) !== false){
                    return response()->json(['status' => 405, 'message' => '禁止非法字节，请重新输入']);
                    break;
                }
            }
        }

        $state = VideoService::storeReview($video_id,$user->id,$content);
        if($state){
            VideoService::setReviewTime($user->id);
            $data = VideoService::lastReview($video_id,$user->id);
            return response()->json(['status' => 0, 'message' => '评论成功','data'=>$data]);
        }
        return response()->json(['status' => 405, 'message' => '评论失败']);
    }

    public function uploadVideoInfo(Request $request){
        DB::enableQueryLog();
        try{
            $data=file_get_contents("php://input");
            if($data == "test"){
                $this->outPutJson(true,"接口连接测试成功",200);
            }
            if(empty($data)){
                foreach ($_POST as $one){
                    $data=$one;
                    break;
                }
            }
            if(!$data)
                $this->outPutJson(false,"来源[0]-任务号[0],失败异常:原因[空数据请求]",400);
            $data=json_decode($data,true);
            $base=$data['base'];
            $id=$data['id'];
            if(isset($data['base']) && $data['base'] == "local"){
                $this->inputLocalSrc($data);
            }else{
                $this->inputAvcaoSrc($data);
            }
        }catch (\Exception $e){
            $this->outPutJson(false,"来源[$base]-任务号[$id],失败异常:原因[".$e->getMessage()."]",400);
        }

        $this->outPutJson(true,"来源[$base]-任务号[$id]影片入库成功",200);

    }
    protected function inputLocalSrc($data){
        $base=$data['base'];
        $baseid=$data['id'];
        $cont=explode(",",$data['imgs']);
        $contents=[];
        foreach ($cont as $item){
            if(!empty($item)){
                $contents[]=$this->switchPath("img",$item,$base,$baseid);
            }
        }
        $thumimg="";
        if(!empty($contents)){
            $thumimg = $contents[0];
        }

        $category = Category::where(array('name' => '其他'))->first();

        if(!$category){
            $category = Category::firstOrNew(array('name' => '其他'));
            $category->identifier=uniqid();
            $category->save();
        }

        if(!$category){
            $this->outPutJson(false,"来源[".$data['base']."]-任务号[".$data['id']."],失败异常:原因[分类入库出错]",400);
        }
        $dbVideo =DB::table('videos')->where("resource_source",$data['base']."_".$data['id'])->first();
        if(!$dbVideo){
            $result=DB::table('videos')->insert([
                'title'     => $data['id'],
                'thumb_img_url'   => $thumimg,
                'video_url'      => $this->switchPath("file",$data['file'],$base,$baseid),
                'video_preview_url'    => $this->switchPath("file",$data['file'],$base,$baseid),
                'contents'=> json_encode($contents),
                'status'     => -1,
                "resource_source"=>$data['base']."_".$data['id'],
                "created_at"=>date("Y-m-d H:i:s",time()),
                "updated_at"=>date("Y-m-d H:i:s",time()),
                'category_id'     => $category->id,
            ]);
            if($result){
                $this->outPutJson(true,"来源[$base]-任务号[$baseid]影片入库成功",200);
            }else{
                $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[视频入库出错]",400);
            }
        }else{
            $this->outPutJson(true,"来源[$base]-任务号[$baseid]影片重复入库,已忽略 ",200);
        }

    }
    protected function inputAvcaoSrc($data){
        $base=$data['base'];
        $baseid=$data['id'];
        $cont=explode(",",$data['imgs']);
        $contents=[];
        foreach ($cont as $item){
            if(!empty($item)){
                $contents[]=$this->switchPath("img",$item,$base,$baseid);
            }
        }
        $thumimg="";
        if(!empty($contents)){
            $thumimg = $contents[0];
        }
        $category = Category::where(array('name' => '其他'))->first();
        if(!$category){
            $category = Category::firstOrNew(array('name' => '其他'));
            $category->identifier=uniqid();
            $category->save();
        }
        if(!$category){
            $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[分类入库出错]",400);
        }
        $name=empty($data['name'])?$data['id']:$data['name'];
        if(preg_match('/([A-Za-z]+)-([0-9]+)/', $name, $mc)){
            $tempcategory = Category::where(array('name' => '黄金正片'))->first();
            $categoryid=$tempcategory->id;
        }elseif(preg_match('/^([\x{4e00}-\x{9fa5}\s\,\，\.\。\?\!]+)$/u', $name, $mc)){
            $tempcategory = Category::where(array('name' => '火爆自拍'))->first();
            $categoryid=$tempcategory->id;
        }else if(preg_match('/^[a-z0-9\#\s\.\?\!\,\:\;\-\(\)]*$/', $name, $mc)){
            $tempcategory = Category::where(array('name' => '欧美专区'))->first();
            $categoryid=$tempcategory->id;
        }else{
            $categoryid=$category->id;
        }
        $dbVideo =DB::table('videos')->where("resource_source",$data['base']."_".$data['id'])->first();
        if(!$dbVideo){
            $data['views']=empty($data['views'])?0:$data['views'];
            $resutl=DB::table('videos')->insert([
                'title'     => empty($data['name'])?$data['id']:$data['name'],
                'thumb_img_url'   => $thumimg,
                'video_url'      => $this->switchPath("file",$data['file'],$base,$baseid),
                'video_preview_url'    => $this->switchPath("file",$data['file'],$base,$baseid),
                'contents'=> json_encode($contents),
                'category_id'     => $categoryid,
                'status'     => -1,
                'rank'     => 255,
                'likes'     => intval(((intval($data['rate']))/100)*$data['views']),
                'dislikes'     => intval((1-((intval($data['rate']))/100))*$data['views']),
                'view_times'     => $data['views'],
                'tags'     =>implode(",",$data['tags']),
                'real_view_times'     => $data['views'],
                'play_times'     => $data['views'],
                "created_at"=>date("Y-m-d H:i:s",time()),
                "updated_at"=>date("Y-m-d H:i:s",time()),
                "resource_source"=>$data['base']."_".$data['id']
            ]);
            if(!empty($data['actor']) && $resutl){
                $actor=$data['actor'];
                $videoid = DB::getPdo()->lastInsertId();
                $actorInfo = DB::select('select id from qvod_actor where  replace(english_name," ","") =:name', [':name'=>strtolower(str_replace(" ","",$actor['name']))]);
                if(!$actorInfo){
                    $bwh=explode("-",$actor['bwh']);
                    if(count($bwh) == 3){
                        $actor['hip']=$bwh[2]."cm";
                        $actor['bust']=$bwh[0]."cm";
                        $actor['waist']=$bwh[1]."cm";
                    }
                    $actorid = Actor::max('id');
                    $actor = Actor::firstOrCreate([
                        'id'=>++$actorid,
                        'name'     =>$actor['name'],
                        'birthplace'     =>$actor['place'],
                        'weight'     =>$actor['weight'],
                        'height'     =>$actor['height']."cm",
                        'birthday'     =>$actor['birthday'],
                        'description'     =>$actor['blog'],
                        'english_name'=>$actor['name'],
                        'name_first_char'=> substr( trim($actor['name']), 0, 1 ),
                        'hip'     =>$actor['hip'],
                        'bust'     =>$actor['bust'],
                        'waist'     =>$actor['waist'],
                    ]);
                }else{
                    foreach ($actorInfo as $item){
                        $actorid=$item->id;
                    }
                }
                DB::table('actor_video')->insert([
                    'actor_id'     =>$actorid,
                    'source'     => 0,
                    'video_id'     => $videoid,
                    "created_at"=>date("Y-m-d H:i:s",time()),
                    "updated_at"=>date("Y-m-d H:i:s",time()),
                ]);

            }
            if(!$resutl){
                $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[视频入库出错]",400);
            }
        }else{
            $this->outPutJson(true,"来源[$base]-任务号[$baseid]影片重复入库,已忽略 ",200);
        }
        $videoid = DB::getPdo()->lastInsertId();
        foreach ($data['tags'] as $item){
            $videotag = DB::table( 'tags' )->where( 'title', $item)->first();
            if(empty($videotag)){
                $tag = Tag::firstOrCreate(array('title' => '其他'));
                $resutl=DB::table( 'tags' )->insert([
                    "title"=>$item,
                    "rank"=>255,
                    "status"=>1,
                    "pid"=>$tag->id,
                    "created_at"=>date("Y-m-d H:i:s",time()),
                    "update_at"=>date("Y-m-d H:i:s",time()),
                ]);
                if(!$resutl){
                    $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[标签入库出错]",400);
                }
                $tagid = DB::getPdo()->lastInsertId();
                $resutl=DB::table( 'video_tag' )->insert([
                    "video_id"=>$videoid,
                    "tag_id"=>$tagid,
                ]);
                if(!$resutl){
                    $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[视频标签关系入库出错]" ,400);
                }
            }else{
                $resutl=DB::table( 'video_tag' )->insert([
                    "video_id"=>$videoid,
                    "tag_id"=>$videotag->id,
                ]);
                if(!$resutl){
                    $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[视频标签关系入库出错]",400);
                }
            }
        }
    }
    protected function switchPath($path,$old,$base,$baseid){
        $patharray=explode('\\',$old);
        if(count($patharray) == 1){
            $patharray=explode('/',$old);
        }
        $patharray=array_reverse($patharray);
        $realpath=[];
        if($path == "img"){//图片取倒序5
            foreach ($patharray as $key =>$one){
                if($key>=5){
                    break;
                }
                $realpath[]=$one;
            }
        }else{//视频取倒序4
            foreach ($patharray as $key =>$one){
                if($key>=4){
                    break;
                }
                $realpath[]=$one;
            }
        }
        $realpath=array_reverse($realpath);
        return "http://video.vbcage.com/".implode("/",$realpath);
    }
    protected function outPutJson($isSuccess,$message,$code){
        /*
         * code
         * 1意外错误
         * 2入参错误
         * 3db错误
         */
        $output['code']=$code;
        $output['msg']=$message;
        $output['success']=$isSuccess;
        $data['input']=[$_POST,$_GET,file_get_contents("php://input")];
        $data['output']=$output;
        $data['sql']=DB::getQueryLog();
        file_put_contents(__DIR__."/../../../storage/logs/uploadVideoInfolog.txt",date("##Y-m-d H:i:s##",time()).json_encode($data).PHP_EOL,FILE_APPEND);
        echo json_encode($output);
        exit;
    }

    public function addMyVideo( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $title = $request->input('title');
        $intro = $request->input('intro');
        $video_url = $request->input('video_url');
        $price = $request->input('price');
        if($title == "" || $intro == "" || $video_url == "" || $price == ""){
            return response()->json( ['status' => 402, 'message' => '参数错误'] );
        }

        $userInfo = DB::table('users')->join('uc_users','users.uc_id','uc_users.id')->select('uc_users.username')->where('users.id',$user->id)->first();
        try{
            $nowTime = date('Y-m-d H:i:s');
            $data['uid'] = $user->id;
            $data['username'] = $userInfo ? $userInfo->username : "null";
            $data['title'] = $title;
            $data['intro'] = $intro;
            $data['video_url'] = $video_url;
            $data['price'] = $price;
            $data['status'] = 0;
            $data['created_at'] = $nowTime;
            $data['updated_at'] = $nowTime;
            $state = DB::table('video_upload')->insert($data);
            if( $state ){
                return response()->json(['status' => 0, 'message' => '上传成功']);
            }
        }catch(\Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }

        return response()->json(['status' => 405, 'message' => '编辑失败']);
    }

    //视频上传
    public function upload( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $video_file = 'file';
        $path = 'Uploads/'.date('Ymd');

//        $img_path = 'Uploads/thumb_img/'.date('Ymd');
        $suffix = $request->file($video_file)->getClientOriginalExtension();
//        $suffixarr=['mp4','flv','wmv','mov'];
//        if(in_array($suffix, $suffixarr)){
            $random   = time().rand(100000, 999999);
            $fileName = $random.'.'.$suffix;
            $imgName  = $random.'.jpg';
            $request->file($video_file)->move($path, $fileName);
            //自动生成目录
//            if(!is_dir($img_path)){
//                mkdir($img_path,0777);
//            }
            $video = $path . '/' .$fileName;
            //生成视频封面
//            exec("ffmpeg -i " . $video . " -y -f image2 -t 0.001 -ss 1 -s 352x240 " . $img_path . "/" . $imgName);
            $video_url = 'http://' . trim($_SERVER['SERVER_NAME'] . '/' . $path.'/'.$fileName,'.');
//            $thumb_img_url = 'http://' . trim($_SERVER['SERVER_NAME'] . '/' . $img_path.'/'.$imgName,'.');
            $data = [
                'video_url' => $video_url,
//                'thumb_img_url' => $thumb_img_url
            ];
            return response()->json(['status' => 0, 'message' => '提交成功','data'=>$data]);
//        }else{
//            return response()->json(['status' => 405, 'message' => '请上传格式为mp4/flv/wmv类型文件']);
//        }
    }

    public function isBought( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $videoId = $request->input('video_id');
        if( !empty($videoId) && $videoId = dehashid($videoId, 'video') ) {
            $isBought = DB::table('video_purchase')->where('video_id',$videoId)->where('buyer_uid',$user->id)->first();
            if($isBought){
                $response = ['status' => 0, 'message' => 'video have purchased'];
            } else {
                $response = ['status' => 403, 'message' => 'video have not purchased'];
            }
        } else {
            $response = ['status' => 404, 'message' => 'video not exist'];
        }
        return response()->json($response);

    }

    public function buyVideo( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $vipInfo = $user->isVip($user);
        if(!$vipInfo){
            return response()->json(['status' => 402, 'message' => '需要会员']);
        }
        $buyerVipTime = floor( ( strtotime($vipInfo->expire_at) - time() ) / 60 );

        $videoId = $request->input('video_id');
        if( !empty($videoId) && $videoId = dehashid($videoId, 'video') ) {
            $videoInfo = Video::query()->where('id',$videoId)->first();
            if(!$videoInfo){
                return response()->json(['status'=>405,'message'=>'视频不存在']);
            }

            $earner_uid = $videoInfo->uid;
            $price = $videoInfo->price;
//            $earner = User::query()->where('id',$earner_uid)->first();

            if( $buyerVipTime < $price ){
                return response()->json(['status' => 402, 'message' => '会员时间不够']);
            }

            $buyer = DB::table('users')->join('uc_users','users.uc_id','uc_users.id')->select('uc_users.username')->where('users.id',$user->id)->first();
            $earner = DB::table('users')->join('uc_users','users.uc_id','uc_users.id')->select('uc_users.username')->where('users.id',$earner_uid)->first();

            try{
                // buyer扣VIP分钟数,earner加分钟数
                UserVip::updateVip($user->id, -1 * $price);
                UserVip::updateVip($earner_uid, $price);

                $nowTime = date('Y-m-d H:i:s');
                $data['buyer_uid'] = $user->id;
                $data['buyer_username'] = $buyer ? $buyer->username : "";
                $data['earner_uid'] = $earner_uid;
                $data['earner_username'] = $earner ? $earner->username : "";
                $data['video_id'] = $videoId;
                $data['price'] = $price;
                $data['created_at'] = $nowTime;
                $data['updated_at'] = $nowTime;
                $state = DB::table('video_purchase')->insert($data);

                if( $state ){
                    return response()->json(['status' => 0, 'message' => '购买成功！']);
                }
            }catch(\Exception $e){
                return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
            }

            $response = ['status' => 0, 'message' => 'OK'];
        } else {
            $response = ['status' => 404, 'message' => 'video not exist'];
        }
        return response()->json($response);

    }

    public function getMyVideo( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $status     = intval($request->input('status'));
        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',10));

        $queryObj = DB::table('video_upload')->where('uid',$user->id);
        if($status != ""){
            $queryObj = $queryObj->where('status',$status);
        }
        $total = $queryObj->count();
        $lists = $queryObj->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)
            ->get(['id','title','intro','video_url','price','status','created_at']);

        $result = [];
        foreach ($lists as $list){
            if( $list->status != 1 ){
                $tmp['id'] = "";
                $tmp['count'] = 0;
                $tmp['title'] = $list->title;
                $tmp['intro'] = $list->intro;
                $tmp['video_url'] = $list->video_url;
                $tmp['price'] = $list->price;
                $tmp['status'] = $list->status;
                $tmp['created_at'] = date('Y-m-d H:i:s',strtotime($list->created_at));
                $result[] = $tmp;
            } else {
                $videoInfo = Video::query()->where('uid',$user->id)->where('title',$list->title)->first();
                if($videoInfo){
                    $tmp['id'] = hashid($videoInfo->id, 'video');
                    $tmp['count'] = DB::table('video_purchase')->where('video_id',$videoInfo->id)->count();
                    $tmp['title'] = $videoInfo->title;
                    $tmp['thumb_img_url'] = $videoInfo->thumb_img_url;
                    $tmp['video_url'] = $videoInfo->video_url;
                    $tmp['price'] = $videoInfo->price;
                    $tmp['status'] = 1;
                    $tmp['created_at'] = date('Y-m-d H:i:s',strtotime($videoInfo->created_at));
                    $result[] = $tmp;
                }
            }
        }

        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];

        $data = [
            'totalVideo'=> $total,
            'page'      => $page,
            'list'      => $result
        ];
        $response = ['status' => 0, 'message' => 'OK', 'data' => $data];
        return response()->json($response);
    }

    public function getPurchase( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',10));
        $uid = $user->id;

        $queryObj = DB::table('video_purchase')->where('buyer_uid',$uid);
        $total = $queryObj->count();
        $lists = $queryObj->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get()->toArray();

        $result = [];
        foreach( $lists as $list ) {
            $videoInfo = Video::query()->where('id',$list->video_id)->first();
            if($videoInfo){
                $tmp['id'] = hashid($videoInfo->id, 'video');
                $tmp['title'] = $videoInfo->title;
                $tmp['count'] = DB::table('video_purchase')->where('video_id',$list->video_id)->count();
                $tmp['thumb_img_url'] = $videoInfo->thumb_img_url;
                $tmp['video_url'] = $videoInfo->video_url;
                $tmp['created_at'] = date('Y-m-d H:i:s',strtotime($videoInfo->created_at));
                $result[] = $tmp;
            }
        }

        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];
        $data = [
            'totalPurchase' => $total,
            'page' => $page,
            'list' => $result
        ];
        $response = ['status' => 0, 'message' => 'OK', 'data' => $data];
        return response()->json($response);
    }

    public function getVideoBill( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $start_time = $request->input( 'start_time' );
        $end_time = $request->input( 'end_time' );
        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',10));
        $uid = $user->id;

//        $queryObj = DB::table('video_purchase')->where('buyer_uid',$uid)->orWhere('earner_uid',$uid);
        $queryObj = DB::table('video_purchase')->where(function ($query) use ($uid){
            $query->where('buyer_uid',$uid)->orWhere('earner_uid',$uid);
        });
        if($start_time){
            $queryObj = $queryObj->where( 'created_at', '>=', $start_time.' 00:00:00' );
        }
        if($end_time){
            $queryObj = $queryObj->where( 'created_at', '<=', $end_time.' 23:59:59' );
        }
        $total = $queryObj->count();
        $lists = $queryObj->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get()->toArray();

        $result = [];
        $totalIncome = 0;
        $totalConsume = 0;
        foreach( $lists as $list ) {
            if($list->buyer_uid == $uid){
                $tmp['username'] = substr($list->earner_username,0,1)."**";
                $tmp['transaction'] = "消费";
                $totalConsume += $list->price;
            }
            if($list->earner_uid == $uid){
                $tmp['username'] = substr($list->buyer_username,0,1)."**";
                $tmp['transaction'] = "收入";
                $totalIncome += $list->price;
            }
            $tmp['video_id'] = $list->video_id;
            $tmp['price'] = $list->price;
            $tmp['time'] = $list->created_at;
            $result[] = $tmp;
        }

        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];
        $data = [
            'totalBill'     => $total,
            'totalIncome'   => $totalIncome,
            'totalConsume'  => $totalConsume,
            'page' => $page,
            'list' => $result
        ];
        $response = ['status' => 0, 'message' => 'OK', 'data' => $data];
        return response()->json($response);
    }


}
