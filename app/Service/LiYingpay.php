<?php
namespace App\Service;
use QrCode;

/**
 * 利盈支付
 */
class LiYingpay{

	const QrCodeWX = '01';
	const QrCodeALI = '02';
	const H5PayWX = '08';
	const H5PayALI = '08';
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
	private $gateway    = 'http://103.78.122.231:8356/payapi.php';
	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/liyingpay";
		$this->signKey = 'DE510D040D1968FC776350A0A35C8235';
		$this->merchantId ='10537';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo,$method);
			$payHtmlUri = $this->genPayHtml($payBody, $orderInfo);
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[LiYingPay]\ttype error\t{$payMethod}\t{$method}");
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
			default:
				\Log::error("[LiYingPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'mch_id'		=> $this->merchantId,
			'trade_type'	=> $paytype,
			'out_trade_no'	=> $orderInfo['out_trade_no'],
			'total_fee'		=> 	$orderInfo['amount']*100,
			'bank_id'		=> 	'',
			'notify_url'	=> 	$this->notify_url,
			'return_url'	=> 	$this->notify_url,
			'time_start'	=> 	date('YmdHis'),
			'nonce_str'		=> 	rand(100000,999999)
		];

		\Log::info('[LiYingPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['sign'] = $sign;

		
		$param = $signParam;
		$param['body'] = '官方支付';
		$param['attach'] = '官方支付';
		$str='<html>';
        $str.='<head>';
        $str.='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $str.='</head>';
        $str.='正在跳转 ...';
        $str.='<body onLoad="document.dinpayForm.submit();">';
        $str.='<form name="dinpayForm" method="post" action="'.$this->gateway.'" target="_self">';
        foreach ($param as $key => $value) {
        	$str .= '<input type="hidden" name="'.$key.'"		  value="'.$value.'" />';
        }

        $str.='</form>';
        $str.='</body>';
        $str.='</html>';
        return $str;
	}

	//加密
	public function genSign($params, $key){
		ksort($params);

		$hbq = http_build_query($params)."&key=".$key;

		$sign = strtoupper( md5( $hbq ) );

		return $sign;
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
		$originSign = $param['sign'];
		unset($param['sign']);

		$callbackSign = $this->genSign($param,$this->signKey);
		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}