<?php
namespace App\Service;
use QrCode;

class HKEasypay{
	private $config = null;

	CONST PAY_TYPE_WECHAT = 'weixin',
		  PAY_TYPE_ALIPAY = 'alipay';
	function __construct(){
		$this->config = config('hkeasypay');
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
			\Log::error("[HKEasypay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createQrOrder($type, $orderInfo){
		$uri = '/Api/Payin/';
		$gatewayUrl = $this->config['host'] . $uri;
		if( $type == 'alipay' ){
			$payType = self::PAY_TYPE_ALIPAY;
		}
		elseif( $type == 'wechat' ){
			$payType = self::PAY_TYPE_WECHAT;
		}
		else{
			\Log::error("[HKEasypay] type error {$type}");
			return false;
		}
		$param = [
			'company_code'	=>	$this->config['company_code'],
			'order_number'	=>	$orderInfo['out_trade_no'],
			'order_amount'	=>	$orderInfo['amount'],
			'pay_id'	=>	$payType,
			'return_url'	=>	$this->config['return_url'],
			'notify_url'	=>	$this->config['notify_url'],
			'timestamp'	=>	date('Y-m-d H:i:s'),
			'base64_memo'	=>	'',
			'sign_type'	=>	'SHA256',
			'version'	=>	'1.0',
			'card_type'	=>	1,
		];
		$param['sign'] = $this->genSign($param);

		$client = new \GuzzleHttp\Client();
		$response = $client->request('POST',$gatewayUrl,[
			'form_params'	=>	$param,
		]);
		$body = (string)$response->getBody();
        $body = trim(mb_substr($body, 0, mb_strpos($body, "</body>") + 7));
        // dd($body);
        $dom = new \HtmlParser\ParserDom(trim($body));
        // dd($body);
        $qrUrl = $dom->find('img',0)->getAttr('src');
        $urlData = parse_url($qrUrl);
        $qrData = urldecode( substr($urlData['query'], 5) );
        return $qrData;
	}	

	public function genSign($data){
		$data['key'] = $this->config['md5_key'];
		$strList = [];
		foreach ($data as $key => $value) {
			$strList[] = $value; 
		}
		$str = join('&',$strList);
		return hash('sha256', $str);
	}

	public function checkNotifySign($data){
		$originSign = $data['sign'];
		unset($data['sign']);
		$sign = $this->genSign($data);
		if( $sign == $originSign ){
			return true;
		}
		else{
			return false;
		}
	}

	public function genQrCode($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.png';
		$uriPath = "/qrcodes/{$filename}";
		$path = public_path($uriPath);
		QrCode::format('png')->size(288)->margin(0)->generate($data, $path);
		return $uriPath;
	}

}