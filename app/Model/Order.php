<?php

namespace App\Model;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Order extends Model
{
    //
    CONST TYPE_VIP = 'vip';

    CONST PAY_METHOD_ALIPAY = 'alipay',
          PAY_METHOD_WECHAT = 'wechat';

    CONST STATUS_UNPAY = 0,
          STATUS_PAY_SUCCESS = 1;

    protected $fillable = ['user_id','title','out_trade_no','pay_info','type', 'item_id','item_info','price','pay_method','status','pay_channel','is_first','channel'];

    public static function createOrder($userId, $type, $itemId, $payMethod, $request){
       try{
            $os   = $request->input('os') ? $request->input('os') : 'android';
            $channel = $request->input('ch') ? $request->input('ch') : 'server';
            //检测是否开启人工充值
            $config = \App\Http\Controllers\UserController::getConfig('artificial_qrcode');
            if($config){
                if( $config['description'] == 1 || ($os=='ios' && $config['description'] == 2) || ($os=='android' && $config['description'] == 3)){

                    return ['type'=>'wap','pay_html_url'=>'http://pay.nmgflower.com/qrcode/'.$itemId,'channel'=>'Serverpay','pay_method'=>$payMethod,'pay_channel'=>'Serverpay','h5'=>true];
                }
            }


            $itemInfo = self::getItemInfo($type, $itemId);
            $outTradeNo = self::genOutTradeNo();

            $amount = $itemInfo->dis_price*$itemInfo->period;

            //获取支付配置
            $payConfig = \App\Model\Pay::getPayByItem($payMethod,$os);
            if(empty($payConfig)){
                return false;
            }
            $preferential = $payConfig->preferential ? $payConfig->preferential : 2;

            //获取随机金额
            $roundMoney = \App\Http\Controllers\VipController::getRoundMoney( $userId, $amount, $preferential );

            $orderInfo = [
                'user_id'   =>  $userId,
                'title' =>  '官方在线支付',//$itemInfo->title,
                'out_trade_no'  =>  $outTradeNo,
                'type'  =>  $type,
                'item_id'   =>  $itemId,
                'item_info' =>  json_encode($itemInfo),
                'pay_method'    =>  $payMethod,
//                'price' =>  $amount-$roundMoney, //有效期*优惠价格
                'price' =>  $amount, //有效期*优惠价格
            ];

            $payInfo = self::genPayInfo($payMethod, $orderInfo, $request, $os, $payConfig);

            if(!$payInfo){
                return false;
            }

            $orderInfo['pay_info'] = json_encode($payInfo);
            $orderInfo['pay_channel'] = $payInfo['pay_channel'];
            $orderInfo['channel']   = $channel;
            $orderInfo['out_trade_no']   = $payInfo['out_trade_no'];

            $order = self::create($orderInfo);
            $payInfo['h5'] = false;
            return $payInfo;
        }
        catch(\Exception $e){
            \Log::error($e->getMessage());
            return $e->getMessage();
        }

    }

    public static function getItemInfo($itemId){
        $itemInfo = Recharge::find($itemId);
        unset($itemInfo['created_at']);
        unset($itemInfo['updated_at']);
        return $itemInfo;
    }

    public static function genOutTradeNo(){
        $uuid = uniqid('',true);
        $suffix = substr($uuid, strpos($uuid, ".") + 1);
        return date('Ymdhis') . $suffix;
    }

    public static function genPayInfo($payMethod, $orderInfo, $request, $os, $payConfig){

        $service  = explode(',', $payConfig->service);
        $method   = $payConfig->method;
        $channel  = $payConfig->parent_pay->first()->alias_name;

        $serviceName = "\App\Service\\" . $channel;

        $payService = new $serviceName();
        $orderInfo = [
            'out_trade_no'  =>  $orderInfo['out_trade_no'],
            'amount'    =>  $orderInfo['price'],
            'title' =>  $orderInfo['title'],
            'user_id'   =>  $orderInfo['user_id'],
            'payerIp' =>  $request->getClientIp(),
        ];

        //博士支付订单号长度有限制
        if($channel == 'Doctorpay'){
            $orderInfo['out_trade_no'] = substr($orderInfo['out_trade_no'],0,strlen($orderInfo['out_trade_no'])-3);
        }

        $is_jump = $payConfig->is_jump;

        $payInfo = $payService->createOrder($payMethod, $method, $orderInfo,$is_jump);

        if(!$payInfo){
            return false;
        }

        if( $method == 'qr' && $channel != 'JuXinpay' && !$is_jump ){
            $payInfo['pay_html_url'] = $request->getSchemeAndHttpHost() . $payInfo['qr_img'];
            unset($payInfo['qr_img']);
        }elseif( $method == 'wap' ){
            $payInfo['pay_html_url'] = $is_jump ?  $payInfo['pay_html_uri'] : $request->getSchemeAndHttpHost() . $payInfo['pay_html_uri'] ;
            unset($payInfo['pay_html_uri']);
        }elseif( $method == 'trans' ){
            $payInfo['message'] = $payInfo['message'];
            $payInfo['account'] = $payInfo['account'];
            $payInfo['amount']  = $orderInfo['amount'];
        }else{
             $payInfo['pay_html_url'] = $is_jump ?  $payInfo['pay_html_uri'] : $request->getSchemeAndHttpHost() . $payInfo['pay_html_uri'] ;
             unset($payInfo['pay_html_uri']);
        }
        $payInfo['channel'] = $channel;
        $payInfo['pay_method'] = $payMethod;
        $payInfo['pay_channel'] = $channel;
        $payInfo['out_trade_no'] = $orderInfo['out_trade_no'];

        $payInfo['type'] = $method;

        return $payInfo;
    }

    public static function getUserInfo($user_id){
        return \App\Model\User::with('ucUser')->find($user_id)->toArray();
    }

    public function processPaySuccess(){
        if( $this->type == self::TYPE_VIP ){
            $itemInfo = json_decode($this->item_info);
            $is_first = Order::where('user_id',$this->user_id)->where('status',1)->first();
            if(!$is_first){
                $this->is_first =1;
            }
            $ret = \App\Model\UserVip::createVip($this->user_id, $itemInfo, $this->out_trade_no, $this->price);
            $this->status = self::STATUS_PAY_SUCCESS;
            $this->save();

            //记录推广红利
            $user = \App\Model\User::with('ucUser')->find($this->user_id)->toArray();

            if(isset($user['uc_user'])){
                if($user['uc_user']['re_signage']){
                    if( Redis::ping() ){
                        //统计
                        Redis::hIncrBy('user_promote_recharge_total',$user['uc_user']['re_signage'],intval($itemInfo->dis_price));
                        Redis::hIncrBy('user_promote_members',$user['uc_user']['re_signage'],1);

                        if( $rebate = Redis::hget('qvod_config','promote_rebate')){
                            $detail = json_decode($rebate,true);
                        }else{
                            $detail = Config::getConfigByAlias('promote_rebate');
                            $detail = json_encode($detail);
                            Redis::hSet('qvod_config','promote_rebate',$detail);
                            $detail = json_decode($detail,true);

                        }
                        $rebate = $detail ? json_decode($detail['content'][0],true) : 0 ;

                        $regRebate = $rebate ? $rebate['recharge'] :0;
                        if($regRebate){
                            //事务
                            DB::beginTransaction();
                            $reSignage = \App\Model\UcUser::find($user['uc_user']['re_signage']);
                            //记录日志
                            $logOption = [
                                    'type'=>1,
                                    'title'=>'推广充值红利',
                                    'user_id'=>$user['uc_user']['re_signage'],
                                    'gift'=>$regRebate[$this->item_id],
                                    'before_gift'=>$reSignage->signage_points,
                                    'after_gift'=>$reSignage->signage_points+$regRebate[$this->item_id],
                                    'status'=>8
                                    ];
                            $gift = \App\Model\UserGift::create($logOption);
                            //添加红利
                            $UcUser = \App\Model\UcUser::where('id',$user['uc_user']['re_signage'])->increment('signage_points',$regRebate[$this->item_id]);
                            if( $gift && $UcUser ){
                                DB::commit();
                            }else{
                                DB::rollback();
                            }
                        }
                    }
                }
            }
            return $ret;
        }
    }

}
