<?php
namespace App\Service;
use QrCode;

/**
 * 星和易支付
 */
class Startpay{

	const QrCode = 'TRADE.SCANPAY',
		  H5Pay = 'TRADE.H5PAY';
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
	private $gateway    = 'http://gate.starspay.com/cooperate/gateway.cgi';//'http://pay.jiatonglian.com/gateway/payment';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/startpay";
		$this->signKey = 'd72487db92e69987dae60693000ecae2'; //test:'797840846e48be64173e9d6024ea5761';
		$this->merchantId ='2017091552010247'; //test:'test001';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo,$method);
			$payHtmlUri = $payBody;
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
				$service = self::QrCode;
				$typeId  = 2;
				break;
			case 'alipay_qr':
				$service = self::QrCode;
				$typeId  = 1;
				break;
			case 'wechat_wap':
				$service = self::H5Pay;
				$typeId  = 2;
				break;
			case 'alipay_wap':
				$service = self::H5Pay;
				$typeId  = 1;
				break;
			default:
				\Log::error("[StartPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'service' => $service,
			'version' => '1.0.0.0',
			'merId'	  => $this->merchantId,
			'typeId'  => $typeId,
			'tradeNo' => $orderInfo['out_trade_no'],
			'tradeDate' =>  date('Ymd'),
			'amount' => $orderInfo['amount'],
			'notifyUrl' => iconv("GBK","UTF-8", $this->notify_url),
			'summary'   => iconv("GBK","UTF-8", $payMethod),
			'extra'     => iconv("GBK","UTF-8", 6),
			'expireTime' => 900,
			'clientIp'  => rand(100,255).'.'.rand(50,150).'.'.rand(100,255).'.'.rand(1,200),
		];

		\Log::info('[StartPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['sign'] = $sign;

		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
	public function genSign($param, $signKey){
		$signStr = sprintf(
					"service=%s&version=%s&merId=%s&typeId=%s&tradeNo=%s&tradeDate=%s&amount=%s&notifyUrl=%s&extra=%s&summary=%s&expireTime=%s&clientIp=%s",
					$param['service'],
					$param['version'],
					$param['merId'],
					$param['typeId'],
					$param['tradeNo'],
					$param['tradeDate'],
					$param['amount'],
					$param['notifyUrl'],
					$param['extra'],
					$param['summary'],
					$param['expireTime'],
					$param['clientIp']
			);

		return md5($signStr.$signKey);
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
					"service=%s&merId=%s&tradeNo=%s&tradeDate=%s&opeNo=%s&opeDate=%s&amount=%s&status=%s&extra=%s&payTime=%s",
					$data['service'],
					$data['merId'],
					$data['tradeNo'],
					$data['tradeDate'],
					$data['opeNo'],
					$data['opeDate'],
					$data['amount'],
					$data['status'],
					$data['extra'],
					$data['payTime']
			);
		$callbackSign = md5($signStr.$this->signKey);
		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}