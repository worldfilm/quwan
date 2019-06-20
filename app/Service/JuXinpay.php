<?php
namespace App\Service;
use QrCode;

/**
 * 聚鑫支付
 */
class JuXinpay{

	/**
	 * 秘钥
	 * @var string
	 */
	private  $signKey = '';

	/**
	 * 商户号
	 * @var string
	 */
	private  $merchantId = '';

	/**
	 * 异步通知地址
	 * @var string
	 */
	private $notify_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway    = 'http://47.92.83.106/gateway/payment';//'http://pay.jiatonglian.com/gateway/payment';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/juxinpay";
		$this->signKey = '46417ac75b390d3594e86826741aa982'; //test:'797840846e48be64173e9d6024ea5761';
		$this->merchantId ='jsqiandu10156'; //test:'test001';
	}

	public function createOrder($type, $method, $orderInfo, $isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($type, $orderInfo);
			$payHtmlUri = $payBody;

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[juxinPay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo){

		//选择支付方式
		switch ($type) {
			case 'wechat':
				$payType = '20';
				break;
			case 'alipay':
				$payType = '40';
				break;
			default:
				\Log::error("[JuxinPay] type error {$type}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'version' => '1.0',
			'agentId' => $this->merchantId,
			'agentOrderId' => $orderInfo['out_trade_no'],
			'payType' => $payType,
			'payAmt' => $orderInfo['amount'],
			'orderTime' => date('YmdHis'),
			'payIp' => '201.201.101.110',
			'notifyUrl' => $this->notify_url
		];
		\Log::info('[JuxinPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['sign'] = $sign;

		return $payUrl = $this->gateway . '?' . urldecode(http_build_query($signParam));
	}

	//加密
	public function genSign($param, $signKey){
		$signStr = '';
		foreach ($param as $key => $value) {
			$signStr .= $value.'|';
		}
		$signStr.= $signKey;
		return md5($signStr);
	}

	public function genPayHtml($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.html';
		$uriPath = "/payhtmls/{$filename}";
		$path = public_path($uriPath);
		file_put_contents($path, $data);
		return $uriPath;
	}

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($data){
		$originSign = $data['sign'];
		unset($data['sign']);
		$signParam = [
			'version'=> $data['version'],
			'agentId'=> $this->merchantId,
			'agentOrderId'=>$data['agentOrderId'],
			'jnetOrderId'=>$data['jnetOrderId'],
			'payAmt'=>$data['payAmt'],
			'payResult'=>$data['payResult']
		];
		$sign = $this->genSign($signParam,$this->signKey);
		if( $sign == $originSign ){
			return true;
		}
		else{
			return false;
		}
	}
}