<?php
namespace App\Service;
use QrCode;

class Goldenpay{
	const PAY_TYPE_WECHAT_QR = 1005,
		  PAY_TYPE_ALIPAY_QR = 1006,
		  PAY_TYPE_ALIPAY_APP = 1008,
		  PAY_TYPE_ALIPAY_WAP = 1010,
		  PAY_TYPE_WECHAT_WAP = 1011;

	const API_VERSION = '1.0.9';
	private $config = null;
	private $serverPulbicKey = null;
	private $privateKey = null;
	function __construct(){
		$this->config = config('goldenpay');
		$this->privateKey = openssl_get_privatekey(file_get_contents($this->config['private_key_path']));
		$this->serverPulbicKey = openssl_pkey_get_public(file_get_contents($this->config['server_public_key_path']));
	}

	public function createOrder($type, $method ,$orderInfo){
		if( $method  ){
			$payBody = $this->createResponse($type, $orderInfo,$method);

			$payHtmlUri = $this->genPayHtml($payBody, $orderInfo);
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri,
			];
		}
		else{
			\Log::error("[SRpay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createResponse($type, $orderInfo,$method){
		switch ($type.'_'.$method) {
			case 'alipay_wap':
				$payType = self::PAY_TYPE_ALIPAY_WAP;
				break;
			case 'alipay_app':
				$payType = self::PAY_TYPE_ALIPAY_APP;
				break;
			case 'alipay_qr':
				$payType = self::PAY_TYPE_ALIPAY_QR;
				break;
			case 'wechat_wap':
				$payType = self::PAY_TYPE_WECHAT_WAP;
				break;
			case 'wechat_qr':
				$payType = self::PAY_TYPE_WECHAT_QR;
				break;
			default:
				\Log::error("[Goldenpay] type error {$type}");
				return false;
				break;
		}

		$param = [
			'merId'	=>	$this->config['appid'],
			// 'version'	=>	self::API_VERSION,
			'terId'	=>	$this->config['clientid'],
			'businessOrdid'	=>	$orderInfo['out_trade_no'],
			'orderName'	=>	'在线充值',
			'tradeMoney'	=>	$orderInfo['amount'] * 100,
			'payType'	=>	$payType,
			'asynURL'	=>	$this->config['notify_url'],
		];

		list($encParam, $sign) = $this->genSign($param);
		\Log::info('Golden sign array'.json_encode($param));
		$reqParam = [
			'sign'	=>	$sign,
			'merId'	=>	$this->config['appid'],
			'version'	=>	self::API_VERSION,
			'encParam'	=>	$encParam,
		];
		$gatewayUrl = $this->config['host'] . '/gateway/orderPay';
		$str='<html>';
        $str.='<head>';
        $str.='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $str.='</head>';
        $str.='正在跳转 ...';
        $str.='<body onLoad="document.dinpayForm.submit();">';
        $str.='<form name="dinpayForm" method="post" action="'.$gatewayUrl.'" target="_self">';
        $str.='<input type="hidden" name="sign"		  value="'.$reqParam['sign'].'" />';
        $str.='<input type="hidden" name="merId"     value="'.$reqParam['merId'].'"/>';
        $str.='<input type="hidden" name="version"     value="'.$reqParam['version'].'"/>';
        $str.='<input type="hidden" name="encParam"     value="'.$reqParam['encParam'].'"/>';
        $str.='</form>';
        $str.='</body>';
        $str.='</html>';

		// $client = new \GuzzleHttp\Client();
		// $response = $client->request('POST',$gatewayUrl,[
		// 	'form_params'	=>	$reqParam,
		// ]);
		// $body = (string) $response->getBody();

		// $data = json_decode($body, true);
		// $retStr = $this->decodeEncParam($data['encParam']);
		return $str;
		// $retData = json_decode($retStr,true);
		// return $retData['code_url'];
	}

	public function genSign($data){
		$encJson = json_encode($data, JSON_UNESCAPED_UNICODE);
		$split = str_split($encJson, 64);
		$encParamEncrypted = '';
		foreach ($split as $part) {
		    openssl_public_encrypt($part,$partialData,$this->serverPulbicKey);//服务器公钥加密
		    $t = strlen($partialData);
		    $encParamEncrypted .= $partialData;
		}
		$encParam = base64_encode($encParamEncrypted);
		openssl_sign($encParamEncrypted, $signInfo, $this->privateKey);
		$sign = base64_encode($signInfo);
		return [$encParam,$sign];
	}

	public function decodeEncParam($encParam){
		$data = base64_decode($encParam);
		$split = str_split($data, 128);
		$ret = '';
		foreach ($split as $key => $value) {
			openssl_private_decrypt($value, $decrypted, $this->privateKey);
			$ret .= $decrypted;
		}
		return $ret;
	}

	public function genQrCode($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.png';
		$uriPath = "/qrcodes/{$filename}";
		$path = public_path($uriPath);
		QrCode::format('png')->size(288)->margin(0)->generate($data, $path);
		return $uriPath;
	}

	public function checkNotifySign($encParam, $sign){
		if( openssl_verify(base64_decode($encParam),base64_decode($sign),$this->serverPulbicKey) ){
			return true;
		}
		else{
			return false;
		}
	}

	public function genPayHtml($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.html';
		$uriPath = "/payhtmls/{$filename}";
		$path = public_path($uriPath);
		file_put_contents($path, $data);
		return $uriPath;
	}
}