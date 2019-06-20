<?php
namespace App\Service;
use QrCode;

class SRpay{
	CONST PAY_TYPE_WECHAT_WAP = 1005,
		  PAY_TYPE_ALIPAY_WAP = 1006,
		  PAY_TYPE_WECHAT_QR = 1004,
		  PAY_TYPE_ALIPAY_QR = 992;

	function __construct(){
		$this->config = config('srpay');
	}

	public function createOrder($type, $method ,$orderInfo){
		if( $method == 'qr' ){
			$hidUrl = $this->createQrOrder($type, $orderInfo);
			$qrUri = $this->genQrCode($hidUrl, $orderInfo);
			return [
				'type'	=>	'qr',
				'qr_img'	=>	$qrUri,
			];
		}
		else{
			\Log::error("[SRpay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createQrOrder($type, $orderInfo){
		if( $type == 'alipay' ){
			$service = self::PAY_TYPE_ALIPAY_QR;
		}
		elseif( $type == 'wechat' ){
			$service = self::PAY_TYPE_WECHAT_QR;
		}
		else{
			\Log::error("[SRpay] type error {$type}");
		}
		$param = [
			'parter'	=>	$this->config['id'],
			'type'	=>	$service,
			'value'	=>	$orderInfo['amount'],
			'orderid'	=>	$orderInfo['out_trade_no'],
			'callbackurl'	=>	$this->config['notify_url'],
			'hrefbackurl'	=>	$this->config['return_url'],
			'payerIp'	=>	$orderInfo['payerIp'],
			'attach'	=>	'test',
		];
		$param['sign'] = $this->genOrderSign($param);
		$url = $this->config['gateway'] . '?' . http_build_query($param);
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        $body = mb_convert_encoding((string)$response->getBody(), 'UTF-8', 'GBK');
        // dd($response->getHeaders());
        if( strstr("error",$body) ){
        	\Log::error("[SRpay]-createQrOrder\t".$body);
        	return false;
        }
		$dom = new \HtmlParser\ParserDom($body);
		$form = $dom->find('form',0);
		$action = $form->getAttr('action');
		$inputList = $dom->find('input');
		$formParam = [];
		foreach ($inputList as $key => $input) {
			$name = $input->getAttr('name');
			$value = $input->getAttr('value');
			$formParam[$name] = $value;
		}
		// dd($this->config['gateway'] . $action);
		$response = $client->request('POST',$this->config['gateway_domain'] . $action,[
			'form_params'	=>	$formParam,
		]);
		// dd($response->);
        $body = mb_convert_encoding((string)$response->getBody(), 'UTF-8', 'GBK');
        $body = mb_substr($body, mb_strpos($body, "<body>"));
        $body = trim(mb_substr($body, 0, mb_strpos($body, "</body>") + 7));
        // dd($body);
        $dom = new \HtmlParser\ParserDom(trim($body));
        // dd($body);
        $hidUrl = $dom->find('input#hidUrl',0)->getAttr('value');
		return $hidUrl;
	}

	public function genQrCode($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.png';
		$uriPath = "/qrcodes/{$filename}";
		$path = public_path($uriPath);
		QrCode::format('png')->size(288)->margin(0)->generate($data, $path);
		return $uriPath;
	}

	public function genOrderSign($param){
		$str = "parter={$param['parter']}&type={$param['type']}&value={$param['value']}&orderid={$param['orderid']}&callbackurl={$param['callbackurl']}{$this->config['key']}";
		return md5($str);
	}

	public function checkNotifySign($param){
		$originSign = $param['sign'];
		$str = "orderid={$param['orderid']}&opstate={$param['opstate']}&ovalue={$param['ovalue']}{$this->config['key']}";
		$sign = md5($str);
		if( $originSign == $sign ){
			return true;
		}
		else{
			return false;
		}
	}
}

