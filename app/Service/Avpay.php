<?php
namespace App\Service;
use QrCode;

/**
 * Av支付
 */
class Avpay{

	/**
	 * 网银支付
	 * @var [type]
	 */
	private $bank = [
			'appId'   => 1001,
			'signKey' => '56fb87eed0eadd396ff9438ffd3b8a66'
	];

	/**
	 * 微信扫码D0
	 * @var [type]
	 */
	private $wechatQr  = [
			'appId'   => 1002,
			'signKey' => '2d28bff83d37b6354e06f4095aa1f35f',
			'payType' =>  1400
	];

	/**
	 * 微信H5配置
	 * @var [type]
	 */
	private $wechatWap = [
			'appId'   =>  1004,
			'signKey' => '26bca89b3449beddae447b9bf02cebd5',
			'payType' =>  1300
	];


	/**
	 * 支付宝H5配置
	 * @var [type]
	 */
	private $alipayWap = [
			'appId'   =>  1003,
			'signKey' => 'daab65d1ae1d024e1b5b3135a515a603',
			'payType' =>  2000
	];

	/**
	 * 支付宝扫码
	 * @var [type]
	 */
	private $alipayQr = [
			'appId'   =>  1007,
			'signKey' => '1ec731f0ddde91a73167808270f7c1c8',
			'payType' =>  1800
	];

	private $rsaPrivateKey = 'MIICXQIBAAKBgQDHE3mt51I8QaLUEUhWWhw7CUUluvbZWRJCK0sJJ0XIvsvNR7Eax834FweA11v8JEgYgPQ3Dw36X6ubfdBr0Hs28o2crTwIyVU8+NErJgFW1epHj6Jen99mKio/v4fqe6oNLQHO69ykIScIWem7fyEfVNcdtFrd6XFBGtju3aijcQIDAQABAoGBAMKYePLTEYGxLzdZBXrTbpEOyLbPAYkIFl7z7s8twnsudg1drPNeqCmAaWc6HMJlvMunEhHX93PsnNTLsTYSfr1FF66kwCyBKOefMkvfzbBbQisIvvE0E9Fn7uFeGOIurmR8vsO2Efgw3tkML0csUCpT0VjOT+Vt5wr9Ar3+pnchAkEA/aNV02lNySATgbABVMDpzTMYdYFy/cdV+x6+lYEzk4LHl4U1PACqqGU7nmo9Zw2rZRyq7ngn5sDHGOl3Yj/e7QJBAMjuEO7xhQYVluUadBzundS7WMctUxrYTZTPKGXzf/5o1/cv44pByW1Is418O/nJuMQNSygfpR2dByJ3CQ4zghUCQGoliA6Q3FfWj7NPmE8C6RXSU2MhyKECYi5VAIeK6a3LJoJ34f55fPI6Y4f5iDbvlpIbPEHOkUxV0zzOwAKjHkUCQHk87IrAwHtDW6ExrQ4oDKPnx1GnT5XLHkTEGqQpoPlpWaaVBr7NozSFwZGFfMrjpNDnFIpJTd/od/2bxaMEAWECQQC+RketfnyLDf1vOnpi2zvGadQxvI0pPhhzRoX4b2/wlE8na5hyfR9TJNnx2FTOQntV6c/VgYRhg37UnVq9QKlL';
	/**
	 * 秘钥
	 * @var string
	 */
	private  $callBackSignKey = '';

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
	 * 同步通知地址
	 * @var string
	 */
	private $success_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway    = 'http://tscand01.3vpay.net/thirdPay/pay/gateway';

	public function __construct(){
		$this->notify_url      = "http://".env("APP_DOMAIN")."/api/pay/notify/avpay";
		$this->success_url     = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId      = '100523'; //test:'test001';
		$this->callBackSignKey = '85a99050b69fb44a4d1145def2dfc5c2';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payHtmlUri = $this->createWapOrder($payMethod, $orderInfo,$method);
			if(!$payHtmlUri){
				return false;
			}

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[startPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$appid    = $this->wechatQr['appId'];
				$signKey  = $this->wechatQr['signKey'];
				$payType  = $this->wechatQr['payType'];
				break;
			case 'alipay_qr':
				$appid    = $this->alipayQr['appId'];
				$signKey  = $this->alipayQr['signKey'];
				$payType  = $this->alipayQr['payType'];
				break;
			case 'wechat_wap':
				$appid    = $this->wechatWap['appId'];
				$signKey  = $this->wechatWap['signKey'];
				$payType  = $this->wechatWap['payType'];
				break;
			case 'alipay_wap':
				$appid    = $this->alipayWap['appId'];
				$signKey  = $this->alipayWap['signKey'];
				$payType  = $this->alipayWap['payType'];
				break;
			default:
				\Log::error("[AvPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$param = [
			'appId'     => $appid,
			'partnerId' => $this->merchantId,
			'imsi'	    => '6.4',
			'deviceCode'  => md5(round(100,255)),
			'channelOrderId' => $orderInfo['out_trade_no'],
			'platform'  =>  round(0,1),
			'body'	    =>  $payMethod,
			'totalFee'  => $orderInfo['amount']*100,
			'payType'   => $payType,
			'timeStamp' => time(),
			'notifyUrl' => iconv("GBK","UTF-8", $this->notify_url),
			'returnUrl'   => iconv("GBK","UTF-8",$this->success_url),
		];

		\Log::info('[AvPay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$signKey);
		//请求参数准备
		$param['sign'] = $sign;

		//请求
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $this->gateway.'?'.http_build_query($param));
		$result = json_decode($response->getBody(),true);

		if(is_array($result)){
			if($result['return_code'] == 0){
				return ( $method == 'wap' ) ? $result['payParam']['pay_info'] : $result['payParam']['code_img_url'];
			}
			\Log::info('[AvPay] request error'.json_encode($result));
			return false;
		}
		\Log::info('[AvPay] request error 具体错误是啥，没记录，哈哈哈哈 '.$response->getBody());
		return false;
	}

	//加密
	public function genSign($param, $signKey){
		$signStr = sprintf(
					"appId=%s&timeStamp=%s&totalFee=%s&key=%s",
					$param['appId'],
					$param['timeStamp'],
					$param['totalFee'],
					$signKey
			);

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
		$signStr = sprintf(
					"channelOrderId=%s&key=%s&orderId=%s&timeStamp=%s&totalFee=%s",
					$data['channelOrderId'],
					$this->callBackSignKey,
					$data['orderId'],
					$data['timeStamp'],
					$data['totalFee']
			);
		$callbackSign = md5($signStr);
		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}