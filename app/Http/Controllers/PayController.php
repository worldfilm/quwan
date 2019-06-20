<?php

namespace App\Http\Controllers;

use App\Model\Order;
use App\Model\UserVip;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;
use App\Service\Lepay;
use Log;
use App\Model\Vip;
use App\Model\Config;
use App\Model\Pay;
use App\Model\Cards;
use App\Service\AlipayService;
use App\Service\Help\PayService;
use App\Service\MTLpay;
use Illuminate\Support\Facades\Redis;
use App\Model\Recharge;
use DB;


class PayController extends BaseController
{



	/**
	 * [payList 支付]
	 * @return [type] [description]
	 */
	public  function pay( Request $request )
	{
		if( !$user = $request->user() ){
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        return response()->json(['status'=>0,'message'=>'success','data'=>url('api/pay/h5')]);
	}


	/**
	 * [payHtml 支付页面]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function payHtml( Request $request )
	{
		$os = $request->input('os');
        $channel = $request->input('ch');
        $apiToken = $request->header('api_token') ? $request->header('api_token') : $request->input('api_token');

        // TODO 配置APP_GETPAYLIST
        $PayList = Pay::getPayListByApi("4");
        $payList = $PayList['payList'];
        $payChannel = json_encode($PayList['payChannel'],320);
        $orderUrl = "http://".env("APP_GETPAYLIST")."/api/pay/order?";

        //$configs = Config::getConfig();
        //$payNotice = $configs['pay_notice']['description'];
        //$customerService = $configs['customer_service']['content'] ? $configs['customer_service']['content'][0] : 0 ;
        $recharge = Recharge::getRechargeList();
        $user = $request->user();
        $uid = $user->id;

        //检测半自动扫码是否开启
        try{
            $apiUrl = "http://".env("APP_GETPAYLIST")."/api/pay/qrStatus?project_id=4";
            $response = file_get_contents($apiUrl);
            $result = json_decode($response,true);
            $wechatQr = in_array("AutoWechatQr",$result['data']) ? "open" : "close";
            $alipayQr = in_array("AutoAlipayQr",$result['data']) ? "open" : "close";
            $qqQr = in_array("AutoQQQr",$result['data']) ? "open" : "close";
            $jdQr = in_array("AutoJDQr",$result['data']) ? "open" : "close";
            $bankQr = in_array("AutoBankQr",$result['data']) ? "open" : "close";
        } catch(\Exception $e){
            $wechatQr = $alipayQr = $qqQr = $jdQr = $bankQr = "close";
        }

        return view('pay.recharge', compact('recharge', 'os', 'apiToken', 'channel', 'user', 'payList', 'payChannel', 'orderUrl', 'uid', 'wechatQr', 'alipayQr', 'qqQr', 'jdQr', 'bankQr'));
	}

    public function getQR( Request $request ){
        $uid = $request->input('uid');
        $itemId = $request->input('item_id');
        $price = $request->input('price');
        $type = $request->input('type');
        switch ($type){
            case 'wechat':
                $payChannel = "AutoWechatQr"; break; //支付后台配置的英文别名
            case 'alipay':
                $payChannel = "AutoAlipayQr"; break;
            case 'qq':
                $payChannel = "AutoQQQr"; break;
            case 'jd':
                $payChannel = "AutoJDQr"; break;
            case 'bank':
                $payChannel = "AutoBankQr"; break;
            default:
                $payChannel = "AutoWechatQr"; break;
        }

        //通过pay_platform的qrAmount接口获取支付金额
        $apiUrl = "http://".env("APP_GETPAYLIST")."/api/pay/qrAmount?project_id=2&item_id=".$itemId."&price=".$price."&pay_channel=".$payChannel."&uid=".$uid;
        $response = file_get_contents($apiUrl);
        $result = json_decode($response,true);
        if(isset($result['message']) && $result['message'] == 'ok'){
            $price = $result['data'];
        } else {
            \Log::error("半自动".$type."扫码获取金额失败,返回:".$response);
            $price = 0;
        }

        //获取后台配置的二维码图片地址
        $qrImg = DB::table('qr_img')->where('type',$type)->where('status',1)->orderByRaw("RAND()")->first();
        $url = $qrImg ? $qrImg->url : "";

        return view('pay.wechatPay',compact('price','url'));
    }


	/**
	 * [payCard 点卡支付页面]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function cardSecretView( Request $request )
	{
		$apiToken = $request->input('api_token');
		$configs = Config::getConfig();
		$customerService = $configs['customer_service']['content'] ? $configs['customer_service']['content'][0] : 0 ;
		return view('pay.payCard',compact('apiToken','customerService'));
	}


	/**
	 * [payCard 点卡支付提交]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function cardSecretPay( Request $request )
	{
		if( !$user = $request->user() ) {
            return response()->json( ['status' => 401, 'message' => '请先登录'] );
        }

        $secret = $request->input('secret');
		if( !$secret ){
			return response()->json( ['status' => 403, 'message' => '请输入卡密'] );
		}

		try{
        	$state  = PayService::payCardSecret( $secret, $user );

	        return response()->json( ['status' => 0, 'message' => '兑换成功', 'data'=> $state ] );

        }catch(\Exception $e){
        	return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
	}

    public  function manualRecharge(){
        $recharge = DB::table('manual_recharge')
            ->select('sort','page_top','description','account')
            ->orderBy('sort','asc')
            ->get()
            ->toArray();
        $manual_notice = Config::getConfigByAlias('manual_recharge_notice');
        $data['list'] = $recharge;
        $data['notice'] = $manual_notice ? $manual_notice['content'][0] : '';
        return response()->json(['status'=>0, 'message'=>'成功', 'data'=>$data]);
    }


	/**
	 * [success 回调]
	 * @return [type] [description]
	 */
	public function success(){
		return  view('pay.success');
	}



	/**
	 * 金付卡回调
	 * @return [type] [description]
	 */
	public function postNotifyGlodenpay(Request $request){
		$data = [
			'merId' => $request->input('merId'),
			'version' => $request->input('version'),
			'encParam' => $request->input('encParam'),
			'sign'	=>	$request->input('sign'),
		];

		$goldenPay = new \App\Service\Goldenpay();
		$ret = $goldenPay->checkNotifySign($data['encParam'], $data['sign']) ;
		if( $ret  ){
			$decodeParamStr = $goldenPay->decodeEncParam($data['encParam']);
			$param = json_decode($decodeParamStr, true);
			$order = \App\Model\Order::where('out_trade_no',$param['orderId'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[postNotifyGlodenpay]\t success\t" .  json_encode($data));
			return "SUCCESS";
		}
		else{
			\Log::info("[postNotifyGlodenpay]\t sign error\t" .  json_encode($data));
			return response('FAIL');
		}
	}


	/**
	 * 精彩付回调
	 * @return [type] [description]
	 */
	public function getReturnBaoteepay(Request $request){
		$data = [
			'trade_status'	=>	$request->input('trade_status'),
			'total_fee'	=>	$request->input('total_fee'),
			'area_out_trade_no'	=>	$request->input('area_out_trade_no'),
			// 'trade_no'	=>	$request->input('trade_no'),
			// 'body'	=>	$request->input('body'),
			// 'gmt_payment'	=>	$request->input('gmt_payment'),
			'sign'	=>	$request->input('sign'),
		];
		$baoteepay = new \App\Service\Baoteepay();
		$ret = $baoteepay->checkNotifySign($data);
		if( $ret  ){
			$order = \App\Model\Order::where('out_trade_no',$data['area_out_trade_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[getReturnBaoteepay]\t success\t" .  json_encode($data));
			return "success";
		}
		else{
			\Log::info("[getReturnBaoteepay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
	}


	/**
	 * 精彩付回调
	 * @return [type] [description]
	 */
	public function postNotifyBaoteepay(Request $request){
		$data = [
			'trade_status'	=>	$request->input('trade_status'),
			'total_fee'	=>	$request->input('total_fee'),
			'area_out_trade_no'	=>	$request->input('area_out_trade_no'),
			'trade_no'	=>	$request->input('trade_no'),
			// 'body'	=>	$request->input('body'),
			'gmt_payment'	=>	$request->input('gmt_payment'),
			'sign'	=>	$request->input('sign'),
		];
		$baoteepay = new \App\Service\Baoteepay();
		$ret = $baoteepay->checkNotifySign($data);
		if( $ret  ){
			$order = \App\Model\Order::where('out_trade_no',$data['area_out_trade_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[postNotifyBaoteepay]\t success\t" .  json_encode($data));
			return "success";
		}
		else{
			\Log::info("[postNotifyBaoteepay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
	}


	/**
	 * 精彩付微信回调
	 * @return [type] [description]
	 */
	public function postNotifyBaoteewxpay(Request $request){
		$data = [
			'area_out_trade_no'	=>	$request->input('area_out_trade_no'),
			'out_transaction_id'	=>	$request->input('out_transaction_id'),
			'result_code'	=>	$request->input('result_code'),
			'time_end'	=>	$request->input('time_end'),
			'total_fee'	=>	$request->input('total_fee'),
			'sign'	=>	$request->input('sign'),
		];
		$baoteewxpay = new \App\Service\Baoteewxpay();
		$ret = $baoteewxpay->checkNotifySign($data);
		if( $ret  ){
			$order = \App\Model\Order::where('out_trade_no',$data['area_out_trade_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[postNotifyBaoteewxpay]\t success\t" .  json_encode($data));
			return "success";
		}
		else{
			\Log::info("[postNotifyBaoteewxpay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
	}


	/**
	 * 乐付支付回调
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function postNotifyLePay(Request $request){
		$ret = $request->toArray();
		Log::info($ret);
		$data = [
			'sign'=>$request->input('sign'),
			'content'=>$request->input('content')
		];

		if( !$data['content'] ){
			\Log::error("[postNotifyLePay]\t response error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
		$lepay = new Lepay();
		$ret   = $lepay->checkNotifySign($data, $data['content']['remark']);

		if($ret){
			$order = \App\Model\Order::where('out_trade_no',$data['area_out_trade_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[postNotifyLePay]\t success\t" .  json_encode($data));
			return "success";
		}
		else{
			\Log::error("[postNotifyLePay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
	}


	/**
	 * 聚鑫支付回调
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function postNotifyJuXinpay(Request $request){
		$ret = $request->toArray();
		\Log::info('【聚鑫支付】回调：'.json_encode($ret));

		$data = [
			'version'=>$request->input('version'),
			'sign' => $request->input('sign'),
			'payAmt'=>$request->input('payAmt'),
			'payResult' =>$request->input('payResult'),
			'content' => $request->input('content'),
			'jnetOrderId'=>$request->input('jnetOrderId'),
			'agentOrderId'=>$request->input('agentOrderId')
		];

		if( !$data['payResult']  ){
			\Log::error("[postNotifyJuXinpay]\t response error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}

		$JuXinpay = new \App\Service\JuXinpay();
		$ret   = $JuXinpay->checkNotifySign($data);


		if($ret){
			$order = \App\Model\Order::where('out_trade_no',$data['agentOrderId'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();

			if( !empty($order) ){
				$order->processPaySuccess();
			}
			\Log::info("[postNotifyJuXinpay]\t success\t" .  json_encode($data));
			return response('success');
		}
		else{
			\Log::error("[postNotifyJuXinpay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
			return response('FAIL');
		}
	}


	/**
	 * 智通宝支付回调
	 * @return [type] [description]
	 */
	public function postNotifyZtbaoPay(Request $request)
	{

		$data = $request->toArray();
		\Log::info('【智通宝支付】回调：'.json_encode($data));
		if($data['trade_status'] == 'SUCCESS'){
			//验证签名
			$ztbao = new \App\Service\ZtBaopay();
			$ret  = $ztbao->checkNotifySign($data);

			if($data['trade_status'] == 'SUCCESS'){
				$order = \App\Model\Order::where('out_trade_no',$data['order_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyZtBaoPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyZtBaoPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('订单号：'.$data['order_no'].'【智通宝支付】回调：'.json_encode($data).' 支付失败');
			return $this->success();
		}
	}


	/**
	 * 星和易支付回调
	 * @return [type] [description]
	 */
	public function postNotifyStartPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【星和易支付】回调：'.json_encode($data));
		if(isset($data['status']) && $data['status'] == 1){
			$option = [
				'service'=>$data['service'],
				'merId'=>$data['merId'],
				'tradeNo'=>$data['tradeNo'],
				'tradeDate'=>$data['tradeDate'],
				'opeNo'=>$data['opeNo'],
				'opeDate'=>$data['opeDate'],
				'amount'=>$data['amount'],
				'status'=>$data['status'],
				'extra'=>$data['extra'],
				'payTime'=>$data['payTime'],
				'sign'=>$data['sign'],
				'notifyType'=>$data['notifyType'],
			];
			//验证签名
			$start = new \App\Service\Startpay();
			$ret  = $start->checkNotifySign($data);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$option['tradeNo'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyZtBaoPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyZtBaoPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('【星和易支付】回调：'.json_encode($data));
			return $this->success();
		}
	}



	/**
	 * AV支付回调
	 * @return [type] [description]
	 */
	public function postNotifyAvPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【AV支付】回调：'.json_encode($data));
		if(isset($data['return_code']) && $data['return_code'] == 0){
			$option = [
				'totalFee'=>$data['totalFee'],
				'channelOrderId'=>$data['channelOrderId'],
				'orderId'=>$data['orderId'],
				'timeStamp'=>$data['timeStamp'],
				'sign'=>$data['sign'],
				'transactionId'=>$data['transactionId'],
			];
			//验证签名
			$avpay = new \App\Service\Avpay();
			$ret  = $avpay->checkNotifySign($option);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$option['channelOrderId'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyAvPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyAvPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('订单号：'.$data['channelOrderId'].'【AV支付】回调：'.json_encode($data).' 支付失败');
			return response('SUCCESS');
		}
	}


	/**
	 * 付联卡支付回调
	 * @return [type] [description]
	 */
	public function postNotifyForPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【付联卡支付】回调：'.json_encode($data));
		if($data['returnCode'] == 'SUCCESS'){
			$option = [
				'bankDate'=>$data['bankDate'],
				'amount'=>$data['amount'],
				'sign'=>$data['sign'],
				'returnCode'=>$data['returnCode'],
				'protocol'=>$data['protocol'],
				'partnerId'=>$data['partnerId'],
				'orderNo'=>$data['orderNo'],
				'charge'=>$data['charge'],
				'status'=>$data['status'],
				'merchantOrderNo'=>$data['merchantOrderNo'],
				'signType'=>$data['signType'],
				'resultCode'=>$data['resultCode'],
			];
			//验证签名
			$forpay = new \App\Service\Forpay();
			$ret  = $forpay->checkNotifySign($option);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$option['merchantOrderNo'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyForPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyForPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('订单号：'.$data['merchantOrderNo'].'【付联卡支付】回调：'.json_encode($data).' 支付失败');
			return response('SUCCESS');
		}
	}


	/**
	 * 博士支付回调
	 * @return [type] [description]
	 */
	public function postNotifyDoctorPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【博士支付】回调：'.json_encode($data));
		if($data['rcode'] == 1){
			$option = [
				'rcode'=>$data['rcode'],
				'rmoney'=>$data['rmoney'],
				'roid'=>$data['roid'],
				'sysid'=>$data['sysid'],
				'syspwd'=>$data['syspwd'],
			];
			//验证签名
			$doctor = new \App\Service\Doctorpay();
			$ret  = $doctor->checkNotifySign($option);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$option['roid'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyDoctorPay]\t success\t" .  json_encode($data));
				return response('success');
			}else{
				\Log::error("[postNotifyDoctorPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('订单号：'.$data['roid'].'博士支付'.json_encode($data).' 支付失败');
			return response('FAIL');
		}
	}


	/**
	 * Boss支付回调
	 * @return [type] [description]
	 */
	public function postNotifyBossPay(Request $request)
	{
		$data = $request->toArray();

		\Log::info('【BOSS支付】回调：'.json_encode($data));
		if(isset($data['Status']) && $data['Status'] == 1){
			$option = [
				'MerchantCode'=>$data['MerchantCode'],
				'OrderId'=>$data['OrderId'],
				'OrderDate'=>$data['OrderDate'],
				'Amount'=>$data['Amount'],
				'OutTradeNo'=>$data['OutTradeNo'],
				'BankCode'=>$data['BankCode'],
				'Sign'=>$data['Sign'],
				'Time'=>$data['Time'],
				'Remark'=>$data['Remark'],
				'Status'=>$data['Status'],
			];
			//验证签名
			$doctor = new \App\Service\Bosspay();
			$ret  = $doctor->checkNotifySign($option);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$option['OrderId'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyBossPay]\t success\t" .  json_encode($data));
				return response('success');
			}else{
				\Log::error("[postNotifyBossPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('订单号：'.$data['roid'].'BOSS支付'.json_encode($data).' 支付失败');
			return response('FAIL');
		}
	}


	/**
	 * 汇天付回调
	 * @return [type] [description]
	 */
	public function postNotifyHTPay(Request $request)
	{
		$data = $request->toArray();
		unset($data['P_ErrMsg']);
		\Log::info('【汇天付】回调：'.json_encode($data));
		if(isset($data['P_ErrCode']) && $data['P_ErrCode']==0){
			//验证签名
			$start = new \App\Service\HTpay();
			$ret  = $start->checkNotifySign($data);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$data['P_OrderId'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyHTPay]\t success\t" .  json_encode($data));
				return response('errCode=0');
			}else{
				\Log::error("[postNotifyHTPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			\Log::info('【汇天付】回调：'.json_encode($data));
			return $this->success();
		}
	}


	/**
	 * 汇合付回调
	 * @return [type] [description]
	 */
	public function postNotifyHHPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【汇合付回调】回调参数：'.json_encode($data));
		if(isset($data['Code']) && $data['Code']=='0'){
			//验证签名
			$start = new \App\Service\HHpay();
			$ret  = $start->checkNotifySign($data);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$data['OutTradeNo'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyHHPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyHHPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			//\Log::info('【汇合付回调】回调：'.json_encode($data));
			return $this->success();
		}
	}


	/**
	 * 金阳支付回调
	 * @return [type] [description]
	 */
	public function postNotifyJinYangPay(Request $request)
	{
		//todo...
	}


	/**
	 * 利盈支付回调
	 * @return [type] [description]
	 */
	public function postNotifyLiYingPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【利盈支付】回调参数：'.json_encode($data));
		if(isset($data['trade_state']) && $data['trade_state']=='SUCCESS'){
			//验证签名
			$start = new \App\Service\LiYingpay();
			$ret  = $start->checkNotifySign($data);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$data['out_trade_no'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyLiYingPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyLiYingPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			//\Log::info('【利盈支付】回调：'.json_encode($data));
			return $this->success();
		}
	}

	/**
	 * 易通支付回调
	 * @return [type] [description]
	 */
	public function postNotifyYtPay(Request $request)
	{
		$data = $request->toArray();
		\Log::info('【易通支付】回调参数：'.json_encode($data));
		if(isset($data['rcode']) && $data['rcode']=='00'){
			//验证签名
			$start = new \App\Service\Ytpay();
			$ret  = $start->checkNotifySign($data);

			if($ret){
				$order = \App\Model\Order::where('out_trade_no',$data['p_orderno'])
									 ->where('status',\App\Model\Order::STATUS_UNPAY)
									 ->first();
				if( !empty($order) ){
					$order->processPaySuccess();
				}
				\Log::info("[postNotifyYtPay]\t success\t" .  json_encode($data));
				return response('SUCCESS');
			}else{
				\Log::error("[postNotifyYtPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
				return response('FAIL');
			}
		}else{
			//\Log::info('【利盈支付】回调：'.json_encode($data));
			return $this->success();
		}
	}

    /**
     * 如一付支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyRyPay(Request $request){
        $data = $request->toArray();
        \Log::info('【如一付】回调参数：'.json_encode($data));
        if(isset($data['P_ErrCode']) && $data['P_ErrCode']=='0'){
            //验证签名
            $start = new \App\Service\Rypay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['P_OrderId'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyRyPay]\t success\t" .  json_encode($data));
                return response('ErrCode=0');
            }else{
                \Log::error("[postNotifyRyPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 速龙支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifySlPay(Request $request){
        $data = $request->toArray();
        \Log::info('【如一付】回调参数：'.json_encode($data,320));
        if(isset($data['trade_status']) && $data['trade_status'] == 'SUCCESS'){
            //验证签名
            $start = new \App\Service\Slpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['order_no'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifySlPay]\t success\t" .  json_encode($data));
                return response('SUCCESS');
            }else{
                \Log::error("[postNotifySlPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['order_no'].'【速龙支付】回调：'.json_encode($data).' 支付失败');
            return $this->success();
        }
    }

    /**
     * 云支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyYunPay(Request $request){
        $data = $request->toArray();
        \Log::info('【云支付】回调参数：'.json_encode($data,320));
        if(isset($data['code']) && $data['code'] == '0'){
            //验证签名
            $start = new \App\Service\YUNpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['orderId'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyYunPay]\t success\t" .  json_encode($data));
                return response('success');
            }else{
                \Log::error("[postNotifyYunPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            \Log::info('订单号：'.$data['orderId'].'【云支付】回调：'.json_encode($data).' 支付失败');
            return $this->success();
        }
    }

    /**
     * 天盛支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyTSPay(Request $request){
        $data = $request->toArray();
        \Log::info('【天盛支付】回调参数：'.json_encode($data,320));
        if(isset($data['Status']) && $data['Status'] == '1'){
            //验证签名
            $start = new \App\Service\TSPay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['OrderId'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyTSPay]\t success\t" .  json_encode($data));
                return response('success');
            }else{
                \Log::error("[postNotifyTSPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            \Log::info('订单号：'.$data['OrderId'].'【天盛支付】回调：'.json_encode($data).' 支付失败');
            return $this->success();
        }
    }

    /**
     * 汇丰支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyHFPay(Request $request){
        $data = $request->toArray();
        \Log::info('【汇丰支付】回调参数：'.json_encode($data,320));
        if(isset($data['state']) && $data['state'] == '1'){
            //验证签名
            $start = new \App\Service\HFPay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['customno'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyHFPay]\t success\t" .  json_encode($data));
                return response('OK');
            }else{
                \Log::error("[postNotifyHFPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['customno'].'【汇丰支付】回调：'.json_encode($data).' 支付失败');
            return $this->success();
        }
    }

    /**
     * 奇迹支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyQJPay(Request $request){
        $data = $request->toArray();
        \Log::info('【奇迹】回调参数：'.json_encode($data));
        if(isset($data['P_Return']) && $data['P_Return']=='1'){
            //验证签名
            $start = new \App\Service\QJPay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['P_OrderId'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyQJPay]\t success\t" .  json_encode($data));
                return response('success');
            }else{
                \Log::error("[postNotifyQJPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            \Log::info('订单号：'.$data['P_OrderId'].'【奇迹支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 速通支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyStPay(Request $request){
        $data = $request->toArray();
        \Log::info('【速通支付】回调参数：'.json_encode($data));
        if(isset($data['opstate']) && $data['opstate']=='0'){
            //验证签名
            $start = new \App\Service\STpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['orderid'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyStPay]\t success\t" .  json_encode($data));
                return response('opstate=0');
            }else{
                \Log::error("[postNotifyStPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 众兴通支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyZxtPay(Request $request){
        $data = $request->toArray();
        \Log::info('【众兴通支付】回调参数：'.json_encode($data));
        if(isset($data['status']) && $data['status'] == '1'){
            //验证签名
            $start = new \App\Service\Zxtpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['sdorderno'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyZxtPay]\t success\t" .  json_encode($data));
                return response('success');
            }else{
                \Log::error("[postNotifyZxtPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 银付支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyYfPay(Request $request){
        $data = $request->toArray();
        \Log::info('【银付】回调参数：'.json_encode($data));
        if(isset($data['result']) && $data['result'] == 'SUCCESS'){
            //验证签名
            $start = new \App\Service\Yfpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['outerCode'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyYfPay]\t success\t" .  json_encode($data));
                return response('success');
            }else{
                \Log::error("[postNotifyYfPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 天机付支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyTjfPay(Request $request){
        $data = $request->toArray();
        \Log::info('【天机付】回调参数：'.json_encode($data));
        if(isset($data['status']) && $data['status'] == '1'){
            //验证签名
            $start = new \App\Service\Tjfpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['tradeNo'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyTjfPay]\t success\t" .  json_encode($data));
                return response('SUCCESS');
            }else{
                \Log::error("[postNotifyTjfPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * TODO 优米付支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyYmfPay(Request $request){
        $data = $request->toArray();
        \Log::info('【优米付】回调参数：'.json_encode($data));
        if(isset($data['orderStatus']) && $data['orderStatus'] == '1'){
            //验证签名
            $start = new \App\Service\Ymfpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['orderNo'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyYmfPay]\t success\t" .  json_encode($data));
                return response('SUCCESS');
            }else{
                \Log::error("[postNotifyYmfPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            return $this->success();
        }
    }

    /**
     * 盛祥支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifySXPay(Request $request){
        $data = $request->toArray();
        \Log::info('【盛祥】回调参数：'.json_encode($data));
        if(isset($data['returncode']) && $data['returncode'] == '1'){
            //验证签名
            $start = new \App\Service\SXpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['orderid'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifySXPay]\t success\t" .  json_encode($data,320));
                return response('ok');
            }else{
                \Log::error("[postNotifySXPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['orderid'].'【盛祥支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 虎云支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyHYPay(Request $request){
        $data = $request->toArray();
        \Log::info('【虎云】回调参数：'.json_encode($data));
        if(isset($data['status']) && $data['status'] == '1'){
            //验证签名
            $start = new \App\Service\HYpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['sdorderno'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyHYPay]\t success\t" .  json_encode($data,320));
                return response('success');
            }else{
                \Log::error("[postNotifyHYPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['sdorderno'].'【虎云支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 百祥付支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyBXFPay(Request $request){
        $data = $request->toArray();
        \Log::info('【百祥付】回调参数：'.json_encode($data));
        if(isset($data['status']) && $data['status'] == '1'){
            //验证签名
            $start = new \App\Service\BXFpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['sdorderno'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyBXFPay]\t success\t" .  json_encode($data,320));
                return response('success');
            }else{
                \Log::error("[postNotifyBXFPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            \Log::info('订单号：'.$data['sdorderno'].'【百祥付支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 聚创支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyJCPay(Request $request){
        $data = $request->getContent();
        \Log::info('【聚创】回调参数：'.json_encode($data));
        $data = json_decode($data,true);
        if(isset($data['msg']) && $data['msg'] == 'SUCCESS'){
            //验证签名
            $start = new \App\Service\JCpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['outOrderNo'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyJCPay]\t success\t" .  json_encode($data,320));
                return response('SUCCESS');
            }else{
                \Log::error("[postNotifyJCPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['outOrderNo'].'【聚创支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * TT支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyTTPay(Request $request){
        $data = $request->getContent();
        \Log::info('【TT】回调参数：'.json_encode($data));
        $data = json_decode($data,true);
        if(isset($data['status']) && $data['status'] == '1'){
            //验证签名
            $start = new \App\Service\TTPay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['sdorderno'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyTTPay]\t success\t" .  json_encode($data,320));
                return response('success');
            }else{
                \Log::error("[postNotifyTTPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('fail');
            }
        }else{
            \Log::info('订单号：'.$data['sdorderno'].'【TT支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 扫呗支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifySBPay(Request $request){
        $data = $request->toArray();
        \Log::info('【扫呗】回调参数：'.json_encode($data,320));
        if(isset($data['orderstatus']) && $data['orderstatus'] == '1'){
            //验证签名
            $start = new \App\Service\SBpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['ordernumber'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifySBPay]\t success\t" .  json_encode($data,320));
                return response('ok');
            }else{
                \Log::error("[postNotifySBPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['ordernumber'].'【扫呗支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 快接支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyKJPay(Request $request){
        $data = $request->toArray();
        \Log::info('【快接】回调参数：'.json_encode($data,320));
        if(isset($data['status']) && $data['status'] == 'Success'){
            //验证签名
            $start = new \App\Service\KJpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['merchant_order_no'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyKJPay]\t success\t" .  json_encode($data,320));
                return response('success');
            }else{
                \Log::error("[postNotifyKJPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['merchant_order_no'].'【快接支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * KK支付回调
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\Response
     */
    public function postNotifyKKPay(Request $request){
        $data = $request->toArray();
        \Log::info('【KK】回调参数：'.json_encode($data,320));
        if(isset($data['orderStatus']) && $data['orderStatus'] == '01'){
            //验证签名
            $start = new \App\Service\KKpay();
            $ret = $start->checkNotifySign($data);

            if($ret){
                $order = \App\Model\Order::where('out_trade_no',$data['prdOrdNo'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info("[postNotifyKKPay]\t success\t" .  json_encode($data,320));
                return response('SUCCESS');
            }else{
                \Log::error("[postNotifyKKPay]\t sign error\t" .  json_encode($data,320) . "\n" . json_encode($request->all()));
                return response('FAIL');
            }
        }else{
            \Log::info('订单号：'.$data['prdOrdNo'].'【KK支付】回调：'.json_encode($data,320).' 支付回调失败');
            return $this->success();
        }
    }

    /**
     * 摩天轮支付回调
     * @param Request $request
     */
    public function postNotifyMtlPay()
    {
        $str = file_get_contents("php://input");
        \Log::info('【摩天轮】回调参数：'.$str);
        parse_str($str,$data);
        \Log::info('【摩天轮】回调参数：'.json_encode($data));
        $signData = [];
        $signData['mch_id'] = $data['mch_id'];
        $signData['payment_time'] = $data['payment_time'];
        $signData['total_fee'] = $data['total_fee'];
        $signData['trade_no'] = $data['trade_no'];
        $signData['out_trade_no'] = $data['out_trade_no'];
        $mtlPay = new \App\Service\MTLpay();

        //先验签
        $sign = $mtlPay->makeSign($signData,\App\Service\MTLpay::SECRETKEY);
        if($sign != $data['sign']){
            \Log::error("[postNotifySlPay]\t sign error\t" .  json_encode($data) . "\n" . json_encode($data));
            return response('fail');
        }else{
            //验签通过
            $order = \App\Model\Order::where('out_trade_no',$data['out_trade_no'])
                ->where('status',\App\Model\Order::STATUS_UNPAY)
                ->first();
            if( !empty($order) ){
                $order->processPaySuccess();
            }
            \Log::info("[postNotifySlPay]\t success\t" .  json_encode($data));
            return response('success');
        }
    }

    /**
     * [postNativeAlipayNotify 原生支付宝回调]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postNativeAlipayNotify( Request $request ){

        $data = $request->toArray();
        \Log::info('['.$data['out_trade_no'].']'.'中转支付宝回调'.json_encode($data));
        if( isset($data['trade_status']) ){
        	$alipay = new AlipayService();
        	$state = $alipay->alipayNotify( $data );
        	if( $state ){

        		$order = \App\Model\Order::where('out_trade_no',$data['out_trade_no'])
                    ->where('status',\App\Model\Order::STATUS_UNPAY)
                    ->first();
                if( !empty($order) ){
                    $order->processPaySuccess();
                }
                \Log::info('['.$data['out_trade_no'].']'.'中转支付宝回调 success：'.json_encode($data));
        		return response('SUCCESS');
        	}else{
        		return response('FAIL');
        	}
        }else{
        	\Log::info( '中转支付宝回调：'.json_encode($data) );
        	return response('FAIL');
        }

    }

    public function notifySuccess( Request $request ){
        $userId = $request->input('uid');
        $money = $request->input('money');
        $itemId = $request->input('item_id');
        $orderId = $request->input('order_id'); //out_trade_no
        if($userId == "" || $money == "" || $itemId == "" || $orderId == ""){
            return response()->json( ['status'=>20001,'message'=>'参数错误'] );
        }

        $itemInfo = Order::getItemInfo($itemId);
        if(!$itemInfo){
            \Log::error("itemId错误导致无法查找itemInfo,itemId:".$itemId);
            return response()->json( ['status'=>20002,'message'=>'无法查找对应到的item'] );
        }

        try{
            DB::transaction( function () use( $userId, $itemInfo ){
                $User = DB::table( 'users' )->where( 'id', $userId )->lockForUpdate()->first(); //already
                // 加钱
                DB::table( 'users' )->where( 'id', $userId )->update( ['deposit'=>($User->deposit + $itemInfo->dis_price*10)] );

            }, 3);
            Redis::hdel( 'user_detail_info', $userId );
            Redis::hdel( 'user_money_info', $userId );
            \Log::info( '---支付平台给用户加钻回调---用户id:' . $userId . '---itemId:' . $itemId . '---订单号:' . $orderId . '---到账钻石数:' . $itemInfo->dis_price*10);
        }catch(\Exception $e){
            \Log::error( '充值账变异常,错误消息' . $e->getMessage());
            throw new PayException($e->getCode());
        }
        return response()->json( ['status'=>100,'message'=>'回调成功'] );
    }


    /**
     * [checkOrder 检测订单信息]
     * @return [type] [description]
     */
    public function checkOrder( Request $request )
    {
        $orderNum = $request->input( 'orderNum', '');
        if( $orderNum ){
            $order = \App\Model\Order::where('out_trade_no',$orderNum)
                    ->where('status',\App\Model\Order::STATUS_PAY_SUCCESS)
                    ->first();
            if( !empty($order) ){
                return response()->json( ['status'=>0,'message'=>'success','data'=>url('api/pay/success')] );
            }
        }

        return response()->json( ['status'=>0,'message'=>'success','data'=>url('api/pay/fail')] );
    }

}