<?php
namespace App\Service;
use QrCode;

/**
 * Av支付
 */
class Ytpay{

	/**
	 * 微信公众号(微信跳转 wap)
	 * @var [type]
	 */
	private $wechatWap = [
			'appId'   =>  901,
	];

	/**
	 * 微信扫码支付
	 * @var [type]
	 */
	private $wechatQr  = [
			'appId'   => 902,
	];

	/**
	 * 支付宝扫码支付
	 * @var [type]
	 */
	private $alipayQr = [
			'appId'   =>  903,
	];

	/**
	 * 支付宝手机（支付宝跳转 wap）
	 * @var [type]
	 */
	private $alipayWap = [
			'appId'   =>  904,
	];

	/**
	 * QQ 手机支付
	 * @var [type]
	 */
	private $qqWap = [
			'appId'   =>  905,
	];

	/**
	 * QQ 扫码支付
	 * @var [type]
	 */
	private $qqQr = [
			'appId'   =>  906,
	];



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
	 * 同步通知地址
	 * @var string
	 */
	private $success_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway    = 'http://jlim.cn/ytpay/order';

	public function __construct(){
		$this->notify_url      = "http://".env("APP_DOMAIN")."/api/pay/notify/ytpay";
		$this->success_url     = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId      = 'pw-b9cb-a37cb2bb3028'; //test:'test001';
		$this->signKey = 'a37cb2bb3028112233';
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
				break;
			case 'alipay_qr':
				$appid    = $this->alipayQr['appId'];
				break;
			case 'qq_qr':
				$appid    = $this->qqQr['appId'];
				break;
			case 'wechat_wap':
				$appid    = $this->wechatWap['appId'];
				break;
			case 'alipay_wap':
				$appid    = $this->alipayWap['appId'];
				break;
			case 'qq_wap':
				$appid    = $this->qqWap['appId'];
				break;
			default:
				\Log::error("[YtPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$param = [
			'p_uno' => $this->merchantId,
			'p_orderno' => $orderInfo['out_trade_no'],
			'p_money'  => $orderInfo['amount']*100,
			'p_type'     => $appid,
			'p_ip'	    => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200),
			'p_nurl' => iconv("GBK","UTF-8", $this->notify_url),
			'p_burl'   => iconv("GBK","UTF-8",$this->success_url),
			'p_body'   => '西瓜干',
			'p_note' => '无添加剂',
		];

		\Log::info('[AvPay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$this->signKey);
		//请求参数准备
		$param['sign'] = $sign;


		//请求
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $this->gateway.'?'.http_build_query($param));
		$result = json_decode($response->getBody(),true);

		if(is_array($result)){
			if($result['rcode'] == '00'){
				return $result['b_purl'];
			}
			\Log::info('[YtPay] request error'.json_encode($result));
			return false;
		}
		\Log::info('[YtPay] request error 具体错误是啥，没记录，哈哈哈哈 '.$response->getBody());
		return false;
	}

	//加密
	public function genSign($param, $signKey){
		ksort($param);
		$signStr = '';
		foreach ($param as $key => $value) {
			$signStr .= $key.'='.$value.'&';
		}
		$signStr .= "key=".$signKey;

		return strtoupper(md5($signStr));
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
		$sign = $this->genSign($data,$this->signKey);

		if( strcasecmp($sign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}