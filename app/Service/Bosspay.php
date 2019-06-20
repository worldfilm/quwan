<?php
namespace App\Service;
use QrCode;

/**
 * boss支付
 */
class Bosspay{

	/**
	 * md5秘钥
	 * @var string
	 */
	private  $md5SignKey = '298baf9ff598672a49d15da7bece3116';

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
	private $gateway    = 'http://pay.api11.com';

	public function __construct(){
		$this->notify_url      = "http://".env("APP_DOMAIN")."/api/pay/notify/bosspay";
		$this->success_url     = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId      = '100004';
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
			\Log::error("[Bosspay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_wap':
				$payType  = 'WECHAT_WAP';
				break;
			case 'alipay_wap':
				$payType  = 'ALIPAY_WAP';
				break;
			default:
				\Log::error("[Bosspay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$param = [
			'MerchantCode'     => $this->merchantId,
			'BankCode'	    => $payType,
			'Amount'    => $orderInfo['amount'],
			'OrderId'   => $orderInfo['out_trade_no'],
			'OrderDate'		=> time(),
			'NotifyUrl'	    =>  $this->notify_url,
			'Remark'    => 1,
		];

		\Log::info('[Bosspay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$this->md5SignKey);

		$param['Sign'] = $sign;

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
	public function genSign($params, $signKey){
		$signText = 'MerchantCode=['.$params['MerchantCode'].']OrderId=['.$params['OrderId'].']Amount=['.$params['Amount'].']NotifyUrl=['.$params['NotifyUrl'].']OrderDate=['.$params['OrderDate'].']BankCode=['.$params['BankCode'].']TokenKey=['.$signKey.']';
		return strtoupper(md5($signText));
	}

	public function pkcs5Pad($text, $blocksize) {
		$pad = $blocksize - (strlen ( $text ) % $blocksize);
		return $text . str_repeat ( chr ( $pad ), $pad );
	}


	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($data){
		$sign = $data['Sign'];
		$signText = 'MerchantCode=['.$data['MerchantCode'].']OrderId=['.$data['OrderId'].']OutTradeNo=['.$data['OutTradeNo'].']Amount=['.$data['Amount'].']OrderDate=['.$data['OrderDate'].']BankCode=['.$data['BankCode'].']Remark=['.$data['Remark'].']Status=['.$data['Status'].']Time=['.$data['Time'].']TokenKey=['.$this->md5SignKey.']';
		$callbackSign = strtoupper(md5($signText));
		if( strcasecmp($callbackSign,$sign) == 0 ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * [genPayHtml 支付跳转页面]
	 * @param  [type] $data      [description]
	 * @param  [type] $orderInfo [description]
	 * @return [type]            [description]
	 */
	public function genPayHtml($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.html';
		$uriPath = "/payhtmls/{$filename}";
		$path = public_path($uriPath);
		file_put_contents($path, $data);
		return $uriPath;
	}

}