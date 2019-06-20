<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Service\AlipayService;
use Illuminate\Support\Facades\Redis;

class VipController extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    const roundMoeny = 'rk_user_round_money_';

    public function getList(Request $request){
    	$data = \App\Model\Vip::getList();
        $response = [
            'status'    =>  0,
            'message'   =>  'OK',
            'data'  =>  $data
        ];
    	return response()->json($response);
    }

    public function setType(Request $request){
    	$userIdentifier = $request->input('identifier');
    	$vipTypeStr = $request->input('vip_type', 'free');
    	$typeMap = [
    		'free'	=>	1,
    		'gold'	=>	2,
    		'platinum'	=>	3,
    	];
    	// dd($userIdentifier);
    	$vipType = isset($typeMap[$vipTypeStr]) ? $typeMap[$vipTypeStr] : 1;
    	$user = \App\Model\User::where('identifier',$userIdentifier)
								->first()
								->update(['vip_type'	=>$vipType]);

    	return response()->json($user);
    }

    public function addVip(Request $request){
        $userIdentifier = $request->input('identifier');
        $user = \App\Model\User::where('identifier',$userIdentifier)->first();
        return \App\Model\UserVip::createVip($user->id,10);
    }

    public function getPurchase(Request $request){
        $user = $request->user();
        if( empty($user) ){
            $response = [
                'status'    =>  401,
                'message'   =>  'need login'
            ];
            return response()->json($response);
        }

        $type = 'vip';
        $itemId = $request->input('id',1);
        $payMethod = $request->input('pay_method','alipay');
        switch( $payMethod ){
            case 'native':
                $alipayStr = self::useNativeAlipay( $user->id, $itemId, $payMethod, $request );
                $order['alipayStr'] = $alipayStr;
                $order['type']  = 'native';
                $order['os']    = $request->input('os', 'ios');
                break;
            default :
                $order = \App\Model\Order::createOrder($user->id, $type, $itemId, $payMethod, $request);
                break;
        }


        if( !empty($order) ){
            $response = [
                'status'    =>  0,
                'message'   =>  'OK',
                'data'  =>  $order
            ];
        }
        else{
            $response = [
                'status'    =>  -1,
                'message'   =>  '支付异常,请重试',
                // 'data'  =>  $order
            ];
        }
        return response()->json($response);
    }

    /**
     * [useNativeAlipay 使用原生alipay]
     * @param  [type] $userId     [description]
     * @param  [type] $rechargeId [description]
     * @param  [type] $payMethod  [description]
     * @param  [type] $request    [description]
     * @param  [type] $money      [description]
     * @return [type]             [description]
     */
    private static function useNativeAlipay( $userId, $rechargeId, $payMethod, $request )
    {
        //准备参数
        $outTradeNo = \App\Model\Order::genOutTradeNo();
        $rechargeInfo  = self::getInfo( $rechargeId );

        $payConfig = \App\Model\Pay::getPayByItem($payMethod,'ios');
        $preferential = $payConfig->preferential ? $payConfig->preferential : 2;

        $amount = $rechargeInfo->dis_price*$rechargeInfo->period;
        //$roundMoeny =  self::getRoundMoney( $userId, $rechargeInfo->dis_price, $preferential );

        //调用扩展生成请求数据
        $aliPayoption = [
            'out_trade_no' => $outTradeNo,
            'price'  => $amount,
            'goods_name' => goods_name($rechargeInfo->dis_price),
            'goods_description' => 'alipaytest'
        ];
        $alipay  = new AlipayService();
        $appData = $alipay->appOrder( $request, $aliPayoption );

        //区分渠道
        $channel = $request->input('ch') ? $request->input('ch') : 'server';

        //生成我们自己的订单
        if( $appData ){
            $orderInfo = [
                'user_id'   =>  $userId,
                'title' =>  '官方支付宝支付',
                'out_trade_no'  =>  $outTradeNo,
                'type'  =>  'vip',
                'item_id'   =>  $rechargeId,
                'item_info' =>  json_encode( $rechargeInfo ),
                'pay_method'    =>  $payMethod,
                'price' =>  $amount,
                'channel' =>  $channel,
            ];

            $payInfo = [];
            
            $payInfo['channel'] = $channel;
            $payInfo['pay_method'] = 'native';
            $payInfo['pay_channel'] = 'alipayNative';
            $payInfo['out_trade_no'] = $orderInfo['out_trade_no'];

            $payInfo['type'] = 'Native';
            $orderInfo['pay_info'] =  json_encode( $payInfo );

            \App\Model\Order::create( $orderInfo );
        }

        return $appData;
    }

    /**
     * [getInfo 获取支付信息]
     * @param  [type] $rechargeId [description]
     * @param  [type] $money      [description]
     * @return [type]             [description]
     */
    private static function getInfo( $rechargeId )
    {
        $rechargeInfo = \App\Model\Vip::find($rechargeId);

        unset( $rechargeInfo['created_at'] );
        unset( $rechargeInfo['updated_at'] );

        return $rechargeInfo;
    }


    /**
     * [getRoundMoney 获取充值随机金额]
     * @param  [type] $userId [description]
     * @param  [type] $money  [description]
     * @return [type]         [description]
     */
    public static function getRoundMoney( $userId, $money, $maxMoney = 2 )
    {
        try{
            Redis::select(4);
            $roundMoeny = Redis::get( self::roundMoeny.$userId );

            if( !$roundMoeny ){
                $roundMoeny =  round( randFloat( 0, $maxMoney ), 2 );
                Redis::set( self::roundMoeny.$userId, $roundMoeny );
                Redis::expireAt( self::roundMoeny.$userId, time()+300 );
            }

            return $roundMoeny;
        }catch(\Exception $e){
           return  round( randFloat( 0, $maxMoney ), 2 );
        }
    }


    public function getPurchaseResult(Request $request){
        $user = $request->user();
        if( empty($user) ){
            $response = [
                'status'    =>  401,
                'message'   =>  'need login'
            ];
            return response()->json($response);
        }
        $outTradeNo = $request->input('out_trade_no');
        $order = \App\Model\Order::where('out_trade_no',$outTradeNo)
                                 ->where('user_id',$user->id)
                                 ->first();
        if( !empty($order) ){
            if( $order->status == \App\Model\Order::STATUS_PAY_SUCCESS ){
                $existVip = \App\Model\UserVip::getUserExistInfo($user->id);
                $user = $request->user();
                $vipInfo = $user->isVip( $user );
                if( $vipInfo ){
                    $vip = [
                        'is_vip'    =>  true,
                        'expire_at' =>  $vipInfo->expire_at,
                    ];
                }
                else{
                    $vip = [
                        'is_vip'    =>  false,
                    ];
                }
                $user = $user->toArray();
                $user['vip'] = $vip;
                $user['id'] = hashid($user['id'],'user');
                $response = [
                    'status'    =>  0,
                    'message'   =>  'OK',
                    'data'  =>  [
                        'pay_status'    =>  1,
                        'user_vip'  =>  [
                            'expire_at' =>  strtotime($existVip->expire_at),
                            'out_trade_no'  =>  $existVip->out_trade_no
                        ],
                        'user'  =>  $user,
                    ],
                ];
            }
            else{
                $response = [
                    'status'    =>  0,
                    'message'   =>  'OK',
                    'data'  =>  [
                        'pay_status'    =>  0,
                    ],
                ];
            }
        }
        else{
            $response = [
                'status'    =>  0,
                'message'   =>  'OK',
                'data'  =>  [
                    'pay_status'    =>  0,
                ],
            ];
        }
        return response()->json($response);
    }
}
