<?php
/**
 * Created by PhpStorm.
 * User: IT
 * Date: 2018/10/16
 * Time: 17:25
 */
namespace App\Console\Commands;
use DB;
use Illuminate\Console\Command;

class SyncOrders extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncorders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'synchronize pay_platform orders to project R';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        date_default_timezone_set('PRC');
        $lastTime = date("Y-m-d H:i:s",strtotime("-20 minutes"));
        $this->info("The starting time is ".$lastTime);
        $apiUrl = "http://".env("APP_GETPAYLIST")."/api/pay/record?project_id=2&created_at=".$lastTime;
        $response = file_get_contents($apiUrl);
        $result = json_decode($response,true);
        if(isset($result['message']) && $result['message'] == 'OK'){
            $orderLists = $result['data']['orderList'];
            $data = [];
            $i = 0;
            foreach ($orderLists as $orderList){
                $isExist = DB::table('orders')->where('out_trade_no',$orderList['out_trade_no'])->first();
                if( !$isExist ){
                    $orderInfo = [
                        'user_id'       => $orderList['user_id'],
                        'title'         => $orderList['title'],
                        'out_trade_no'  => $orderList['out_trade_no'],
                        'type'          => $orderList['type'],
                        'item_id'       => $orderList['item_id'],
                        'item_info'     => $orderList['item_info'],
                        'pay_method'    => $orderList['pay_method'],
                        'price'         => $orderList['price'],
                        'pay_info'      => $orderList['pay_info'],
                        'status'        => $orderList['status'],
                        'created_at'    => $orderList['created_at'],
                        'updated_at'    => $orderList['updated_at'],
                        'channel'       => $orderList['channel'],
                        'pay_channel'   => $orderList['pay_channel'],
                    ];
                    $data[] = $orderInfo;
                }else if( $isExist && $isExist->status != $orderList['status'] ){
                    DB::table('orders')->where('out_trade_no',$orderList['out_trade_no'])->update(['status' => $orderList['status']]);
                    $i++;
                }
            }
            DB::table('orders')->insert($data);
            $this->info("synchronize ".count($data)." record");
            $this->info("update $i record");
        } else {
            $this->info("fail");
        }
    }

}