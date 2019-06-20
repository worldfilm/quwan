<?php
namespace App\Service;
use QrCode;

class Microwepay{
	private $config = null;

	CONST PAY_TYPE_ALIPAY_QR = 'ALIPAYSCAN',
		  PAY_TYPE_WECHAT_QR = 'WEIXIN_NATIVE',
		  PAY_TYPE_ALIPAY_H5 = 'ALIPAYWAP';

	function __construct(){
		$this->config = config('microwepay');
	}

	public function createOrder($type, $method ,$orderInfo){
		if( $method == 'qr' ){
			$hidUrl = $this->createQrOrder($type, $orderInfo);
			$qrUri = $this->genQrCode($hidUrl, $orderInfo);
			return [
				'type'	=>	'qr',
				'qr_img'	=>	$qrUri,
				'code_url'	=>	$hidUrl,
			];
		}
		else{
			\Log::error("[Microwepay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createQrOrder($type, $orderInfo){
		$uri = '/Api/Payin/';
		if( $type == 'alipay' ){
			$payType = self::PAY_TYPE_ALIPAY_QR;
		}
		elseif( $type == 'wechat' ){
			$payType = self::PAY_TYPE_WECHAT_QR;
		}
		else{
			\Log::error("[Microwepay] type error {$type}");
			return false;
		}
		$param = [
			'svcName'	=>	'paygate.thirdpay',
			'merId'	=>	$this->config['merId'],
			'merchOrderId'	=>	$orderInfo['out_trade_no'],
			'amt'	=>	$orderInfo['amount'] * 100,
			'ccy'	=>	'CNY',
			'tranTime'	=>	date('Y:m:d H:i:s'),
			'tranChannel'	=>	$payType,
			'merUrl'	=>	'http://abc.com',
			'retUrl'	=>	$this->config['notify_url'],
			'merData'	=>	'0',
			'pName'	=>	'pay',
			'pCat'	=>	'pay',
			'pDesc'	=>	'pay',
			'tranType'	=>	$payType,
			'merUserId'	=>	$orderInfo['user_id'],
			'terminalId'	=>	$orderInfo['user_id'],
			'terminalType'	=>	'0',
			'productType'	=>	'4',
			'userIp'	=>	$orderInfo['payerIp'],
		];
		$param['md5value'] = $this->genSign($param);
		$url = $this->config['gateway'] . '/fm/';
		$client = new \GuzzleHttp\Client();
		$response = $client->request('POST',$url,[
			'form_params'	=>	$param,
		]);
		$body = (string)$response->getBody();	
		dd($body);

	}
// md5value=MD5(svcName+merId+ merchOrderId +amt+ccy +tranTime+ tranChannel +retUrl +merUserId+ tranType + terminalType +terminalId+ productType+ userIp+ key)

	public function genSign($data){
		$md5Str = $data['svcName'] . $data['merId'] . $data['merchOrderId'] . $data['amt'] . $data['ccy'] . $data['tranTime'] . $data['tranChannel'] . $data['retUrl'] . $data['merUserId'] . $data['tranType'] . $data['terminalType'] . $data['terminalId'] . $data['productType'] . $data['userIp'] . $this->config['key'];
		return strtoupper( md5($md5Str) );
	}
}