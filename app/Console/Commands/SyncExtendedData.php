<?php

namespace App\Console\Commands;

use DB;
use App\Model\Advert;
use App\Model\Mongo\Advert as mongoAdvert;
use Illuminate\Console\Command;

class SyncExtendedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:extendedData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync extended data ex:advert、config....';

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
        $this->info('sync advert to mongo start');
        ignore_user_abort(true);
        date_default_timezone_set('PRC');
        set_time_limit(0);

        $i = 0;
        $adverts = Advert::get();

        foreach ($adverts as $key => $advert) {
            mongoAdvert::where('id',$advert->id)->delete();

            $option = [
                    'id'=>$advert->id,
                    'title'=>$advert->title,
                    'thumb_img_url'=>$advert->thumb_img_url,
                    'description'=>$advert->description,
                    'service'=>$advert->service,
                    'position'=>$advert->position,
                    'url'=>$advert->url,
                    'type'=>$advert->type,
                    'start_time'=>$advert->start_time,
                    'end_time'=>$advert->end_time,
                    'rank'=>$advert->rank,
                    'status'  => $advert->status,
                    'admin_id' => $advert->admin_id,
                    'created_at'=>$advert->created_at,
                    'updated_at'=>$advert->updated_at
            ];

            mongoAdvert::where('id',$advert->id)->insert($option);
            $i++;
        }

        $this->info('sync advert to mongo end totle num:'.$i);
    }
}
