<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SyncVideoViews extends Command
{
    const video_inc_views      = 'video_inc_views',
          video_inc_play_times = 'video_inc_play_times',
          video_inc_real_views = 'video_inc_real_views',
          videoCollectTimes = 'video_collect_times',
          detailCacheKey = 'video_detail_cache',
          videoIncLikeTimes  = 'video_inc_like_times',
          videoIncDislikeTimes = 'video_inc_dislike_times';


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncvideo:views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sycn video view times or play times  of  10 m';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('sync video detail start');

        ignore_user_abort(true);
        date_default_timezone_set('PRC');
        set_time_limit(0);

        $i = 0;

        //批量同步
        self::syncVideos($i);

        $this->info('sync video detail end');
    }


    private static function syncVideos($i)
    {
        $videos = [];
        //查看
        $views = Redis::hgetAll(self::video_inc_views);

        foreach ($views as $key => $value) {

            if($value < 1 ){
                break;
            }
            $videos[$key]['view_times'] = $value;

            //清除video view缓存
            Redis::hset(self::video_inc_views,$key,0);//清0
            //真实查看次数
            $realViewTimes = Redis::hget(self::video_inc_real_views,$key);
            $videos[$key]['real_view_times'] = $realViewTimes;
            //清除video realview缓存
            Redis::hset(self::video_inc_real_views,$key,0);//清0
        }

        //播放
        $playTimes = Redis::hgetAll(self::video_inc_play_times);
        foreach ($playTimes as $key => $value) {
            if($value < 1 ){
                break;
            }
            $videos[$key]['play_times'] = $value;
            //清除video play缓存
            Redis::hset(self::video_inc_play_times,$key,0);//清0
        }

        //收藏
        $collectTimes = Redis::hgetAll(self::videoCollectTimes);
        foreach ($collectTimes as $key => $value) {
            if($value < 1 ){
                break;
            }
            $videos[$key]['collect_times'] = $value;
            //清除video collect缓存
            Redis::hset(self::videoCollectTimes,$key,0);//清0
        }

        //好评
        $like = Redis::hgetAll(self::videoIncLikeTimes);
        foreach ($like as $key => $value) {
            if($value < 1 ){
                break;
            }
            $videos[$key]['likes'] = $value;
            //清除video like缓存
            Redis::hset(self::videoIncLikeTimes,$key,0);//清0
        }

        //差评
        $dislike = Redis::hgetAll(self::videoIncDislikeTimes);
        foreach ($like as $key => $value) {
            if($value < 1 ){
                break;
            }
            $videos[$key]['dislikes'] = $value;
            //清除video dislike缓存
            Redis::hset(self::videoIncDislikeTimes,$key,0);//清0
        }

        foreach ($videos as $key => $value) {

            $video = DB::table('videos')->select('view_times','play_times','collect_times','likes','dislikes','real_view_times')->where('id',$key)->first();
            $option = [];
            if(isset($value['view_times'])){
                $option['view_times'] = $value['view_times']+$video->view_times;
            }
            if(isset($value['play_times'])){
                $option['play_times'] = $value['play_times']+$video->play_times;
            }
            if(isset($value['collect_times'])){
                $option['collect_times'] = $value['collect_times']+$video->collect_times;
            }
            if(isset($value['likes'])){
                $option['likes'] = $value['likes']+$video->likes;
            }
            if(isset($value['dislikes'])){
                $option['dislikes'] = $value['dislikes']+$video->dislikes;
            }
            if(isset($value['real_view_times'])){
                $option['real_view_times'] = $value['real_view_times']+$video->real_view_times;
            }

            DB::table('videos')->where('id',$key)->update($option);
            try{
                DB::connection('mongodb')->table('videos')->where('id',$key)
                       ->update($option, ['upsert' => true]);
            }
            catch(\Exception $e){
                echo 'mongodb dis connection';
            }
            Redis::hdel(self::detailCacheKey,$key);
            $i++;
            if($i%5 == 0){
                sleep(25);
            }
        }
    }

}
