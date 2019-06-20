<?php

namespace App\Console\Commands;

use DB;
use App\Model\Video;
use App\Model\Mongo\Video as mongoVideo;
use Illuminate\Console\Command;

class SyncVideoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncvideo:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync video data once';

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
        $this->info('sync video to mongo start');
        ignore_user_abort(true);
        date_default_timezone_set('PRC');
        set_time_limit(0);

          $i = $r = 0;


        $lastMongoVideo  =  mongoVideo::orderBy('id','DESC')->first();

        //mongo为空  刷全部数据
        if( !$lastMongoVideo ){
            while (1) {
                $num = $r*1000;
                $videos = DB::table( 'videos' )->where( 'status', 0 )->skip($num)->take(1000)->get();
                if($videos->isEmpty()){
                    break;
                }

                foreach ($videos as $key => $video) {
                    mongoVideo::where('id',$video->id)->delete();
                    $option = [
                            'id'=>$video->id,
                            'title'=>$video->title,
                            'thumb_img_url'=>$video->thumb_img_url,
                            'video_url'=>$video->video_url,
                            'video_preview_url'=>$video->video_preview_url,
                            'category_id'=>$video->category_id,
                            'likes'=>$video->likes,
                            'dislikes'=>$video->dislikes,
                            'view_times'=>$video->view_times,
                            'real_view_times'=>$video->real_view_times,
                            'collect_times'=>$video->collect_times,
                            'play_times'  => $video->play_times,
                            'description' => $video->description,
                            'contents'    => $video->contents,
                            'tags'        => $video->tags,
                            'status'      => $video->status,
                            'rank'        => $video->rank,
                            'uid'         => $video->uid,
                            'price'       => $video->price,
                            'created_at'  => $video->created_at,
                            'updated_at'  => $video->updated_at,
                    ];
                    mongoVideo::where('id',$video->id)->insert($option);
                    $i++;
                }
                $r++;
            }
            $this->info('sync video to mongo end totle num:'.$i);
            die;
        }

        //mongo不为空  新增数据
        $videos = DB::table('videos')->where('status', 0)->where('id','>', $lastMongoVideo->id)->get();
        foreach ($videos as $key => $video) {
            //mongoVideo::where('id',$video->id)->delete();
            $option = [
                    'id'=>$video->id,
                    'title'=>$video->title,
                    'thumb_img_url'=>$video->thumb_img_url,
                    'video_url'=>$video->video_url,
                    'video_preview_url'=>$video->video_preview_url,
                    'category_id'=>$video->category_id,
                    'likes'=>$video->likes,
                    'dislikes'=>$video->dislikes,
                    'view_times'=>$video->view_times,
                    'real_view_times'=>$video->real_view_times,
                    'collect_times'=>$video->collect_times,
                    'play_times'  => $video->play_times,
                    'description' => $video->description,
                    'contents'    => $video->contents,
                    'tags'        => $video->tags,
                    'status'      => $video->status,
                    'rank'        => $video->rank,
                    'created_at'  => $video->created_at,
                    'updated_at'  => $video->updated_at,
            ];
            mongoVideo::where('id',$video->id)->insert($option);
            $i++;
        }

        $this->info('sync video to mongo end totle num:'.$i);
    }
}
