<?php

namespace App\Service\Help;

use Exception;
use DB;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\Redis;
use App\Model\UcUser;
use App\Model\User;
use App\Model\UserGift;
use App\Model\Order;
use App\Model\Recharge;
use App\Model\Constellation;
use App\Model\UserConsumptionLog;

use App\Model\Zb\ZbTZhubo;
use App\Model\Zb\ZbTUserCars;
use App\Model\Zb\ZbTUserAttention;
use App\Model\Zb\ZbTConsumeLogs;
use App\Model\Zb\ZbTRecrecordsLogs;
use App\Model\Zb\ZbTUserBlacklist;
use App\Model\Zb\ZbTExchangeLog;

use App\Exceptions\PayException;
use App\Exceptions\UserException;

/**
 *   消费基础服务
 *   @auther morgan
 */
class PayService
{
    const cardsSecret = 'cards_secret';

    public function __construct(){}


    /**
     * [buyVideo 充值卡密]
     * @param  [type] $userId  [description]
     * @param  [type] $videoId [description]
     * @return [type]          [description]
     */
    public static function payCardSecret( $secret, $user )
    {

        try{
            Redis::select(8);
            if( !$cardId = Redis::hget(self::cardsSecret, $secret ) ){
                throw new Exception("", 2001);  
            }
            $data['card_id'] = $cardId;
            $data['user_id'] = $user->id;
            $data['secret']  = $secret;
            DB::transaction( function () use( $data ){
                //锁定卡密
                $cardDetail = DB::table( 'cards' )->where( 'id', $data['card_id'] )->lockForUpdate()->first();
                $userVips = DB::table( 'users_vips' )->where(['user_id'  =>  $data['user_id']])
                            ->where('expire_at', '>', date('Y-m-d H:i:s'))
                            ->orderBy('id','desc')
                            ->first();

                //查询最后vip到期时间并加30天
                if( $userVips ){
                    $lastExpire = strtotime("+30 days",strtotime($userVips->expire_at));
                }
                else{
                    $lastExpire = strtotime("+30 days");
                }

                DB::table( 'users_vips' )->insert([
                    'user_id'   =>  $data['user_id'],
                    'amount'    =>  '59',
                    'expire_at' =>  date('Y-m-d H:i:s', $lastExpire),
                    'out_trade_no'  =>  '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'type'      => 2,
                ]);
                //修改卡密状态 并 清除redis
                DB::table( 'cards' )->where( 'id', $data['card_id'] )->update(['use_id'=>$data['user_id'],'status'=>1]);
                Redis::hdel( self::cardsSecret, $data['secret'] );
            });

        }catch(\Exception $e){
            throw new PayException($e->getCode());
        }

    }
}