<?php
namespace App\Service;
use QrCode;

/**
 * 汇合支付
 */
class HHpay{

	/**
	 * wechat扫码
	 */
	const QrCodeWX = '2';

	/**
	 * qqWap
	 */
	const H5PayQQ = '11';

	/**
	 * alipay扫码
	 */
	const QrCodeALI = '6';

	/**
	 * wechat wap
	 */
	const H5PayWX = '7';

	/**
	 * Alipay wap (? 文档中没有这个)
	 */
	const H5PayALI = '7';

	/**
	 * 京东钱包
	 */
	const H5PayJD = '9';

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
	private $gateway    = 'https://pay.huihepay.com';
	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/hhpay";
		$this->signKey = '2b9e1a2b993d955cd6ba321d6b58d931';
		$this->merchantId ='201711241427277844';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo,$method);

			if(!$payBody){
				return false;
			}

			//$payHtmlUri = $this->genPayHtml($payBody, $orderInfo);
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payBody
			];
		}
		else{
			\Log::error("[HHPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$paytype = self::QrCodeWX;
				break;
			case 'alipay_qr':
				$paytype = self::QrCodeALI;
				break;
			case 'wechat_wap':
				$paytype = self::H5PayWX;
				break;
			case 'alipay_wap':
				$paytype = self::H5PayALI;
				break;
			case 'qq_wap':
				$paytype = self::H5PayQQ;
				break;
			case 'jd_wap':
				$paytype = self::H5PayJD;
				break;
			default:
				\Log::error("[HHPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'AppId'		=> $this->merchantId,
			'Method'		=> 'trade.page.pay',
			'Format'		=> 'JSON',
			'Charset'		=> 'UTF-8',
			'Version'		=> '1.0',
			'Timestamp'		=> date('Y-m-d H:i:s'),
			'PayType'		=> $paytype,
			'OutTradeNo'		=> $orderInfo['out_trade_no'],
			'TotalAmount'		=> $orderInfo['amount'],
			'Subject'		=> '官方支付',
			'Body'		=> '官方支付',
			'NotifyUrl'		=> $this->notify_url,
		];

		\Log::info('[HHPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['SignType'] = 'MD5';
		$signParam['Sign'] = $sign;

		//请求
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->gateway);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signParam));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        $result = json_decode($response,true);

        if($result['Code'] == '0' && isset($result['QrCode'])){
        	return $result['QrCode'] ;
        }else{
        	return false;
        }
	}

	//加密
	public function genSign($param, $signKey){
		ksort($param);
		foreach ($param as $key => $value) {
			if(empty($value) && $value !== '0'){
				unset($param[$key]);
			}
		}
		$signStr = urldecode( http_build_query($param) ) . $signKey;
		//dd($signStr);
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
	public function checkNotifySign($param){
		$originSign = $param['Sign'];
		unset($param['Sign']);
		unset($param['SignType']);

		$callbackSign = $this->genSign($param,$this->signKey);
		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}