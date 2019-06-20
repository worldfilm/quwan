<?php

namespace App\Console\Commands;

use App\Model\UcUser;
use Illuminate\Console\Command;

class SyncNickname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:nickname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync user-nickname to ucuser-nickname';

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
        $h = 0;
        for($i=0;$i<1000000;$i++){
            $ucuser = UcUser::with('user')->where('id','>=',$h)->paginate(1000);
            if(count($ucuser) < 1){
                break;
            }
            foreach ($ucuser as $key => $value) {
                if($value->user){
                    UcUser::where('id',$value->id)->update(['nickname'=>$value->user->nickname]);
                }
            }

            $h += 1000;
            $this->info('sync  num:'.$h);
        }
       $this->info('sync  totle num:'.$h);
    }
}
