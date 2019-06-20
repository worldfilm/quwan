<?php
namespace App\Service;
use QrCode;

class Baoteepay{
	private $config = null;
	function __construct(){
		$this->config = config('baoteepay');
	}

	public function createOrder($type, $method ,$orderInfo,$isJump=0){

		if( $method == 'wap' ){
			$payBody = $this->createWapOrder($type, $orderInfo);
			if(!$payBody){
				return false;
			}
			return [
				'type'	=>	'wap',
				'pay_html_uri'	=>	$payBody
			];
		}elseif( $method == 'qr' ){
			$codeUrl = $this->createQrOrder($type, $orderInfo);
			$qrUri = $this->genQrCode($codeUrl, $orderInfo);
			return [
				'type'	=>	'qr',
				'qr_img'	=>	$qrUri,
			];

		}
		else{
			\Log::error("[Baoteepay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo,$isJump = 0){
		if( $type == 'alipay' ){
			$payType = 'alipay';
		}
		else{
			\Log::error("[Baoteepay] type error {$type}");
			return false;
		}
		$param = [
			'area'	=>	$this->config['area'],
			'area_notify_url'	=>	$this->config['notify_url'],
			// 'area_return_url'	=>	$this->config['return_url'],
			'area_out_trade_no'	=>	$orderInfo['out_trade_no'],
			'is_jump'   =>  $isJump,
			'subject'	=>	'onlinepay',
			'total_fee'	=>	$orderInfo['amount'],
			// 'app_pay'	=>	'Y',
			'body'	=>	'pay',
		];

		// dd($param);
		$sign = $this->genSign($param,['area','area_out_trade_no','subject','total_fee']);
		$param['sign'] = $sign;

		$url = $this->config['gateway'] . '/AliPay/JcSaoMaPayServlet';//XingYeSaoMaPayServlet';
		$client = new \GuzzleHttp\Client();

		$response = $client->request('POST',$url . '?' . urldecode(http_build_query($param)),['connect_timeout'=>30]);
		$body = (string)$response->getBody();
		\Log::info("[Baoteepay response] ".$body);
		$data = json_decode($body, true);
		if(is_array($data)){
			if( $data['code'] == 1 ){
				return $data['code_url'];
			}
			\Log::info(json_encode($data));
			return false;
		}else{
			\Log::info($body);
			return false;
		}

	}

	public function createQrOrder($type, $orderInfo){
		if( $type == 'alipay' ){
			$payType = 'alipay';
		}
		else{
			\Log::error("[Baoteepay] type error {$type}");
			return false;
		}
		$param = [
			'area'	=>	$this->config['area'],
			'area_notify_url'	=>	$this->config['notify_url'],
			'area_return_url'	=>	$this->config['return_url'],
			'area_out_trade_no'	=>	$orderInfo['out_trade_no'],
			'subject'	=>	'onlinepay',
			'total_fee'	=>	$orderInfo['amount'],
			'app_pay'	=>	'Y',
			'body'	=>	'pay',
		];
		$sign = $this->genSign($param,['area','area_out_trade_no','subject','total_fee']);
		$param['sign'] = $sign;
		// dd($param);
		$url = $this->config['gateway'] . '/alipay/AliSaoMaOrderServlet';
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET',$url . '?' . urldecode(http_build_query($param)));
		$body = (string)$response->getBody();

		$data = json_decode($body, true);
		return isset($data['code_url']) ? $data['code_url'] : false;
	}

	public function genSign($param, $signKeys){
		$signList = [];
		foreach ($signKeys as $key) {
			$signList[$key] = $param[$key];
		}
		ksort($signList);
		$signList['key'] = $this->config['key'];
		$signStr = urldecode(http_build_query($signList));
		return strtoupper(md5($signStr));
	}

	public function genQrCode($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.png';
		$uriPath = "/qrcodes/{$filename}";
		$path = public_path($uriPath);
		QrCode::format('png')->size(288)->margin(0)->generate($data, $path);
		return $uriPath;
	}

	public function genPayHtml($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.html';
		$uriPath = "/payhtmls/{$filename}";
		$path = public_path($uriPath);
		file_put_contents($path, $data);
		return $uriPath;
	}

	public function checkNotifySign($data){
		$originSign = $data['sign'];
		unset($data['sign']);
		$sign = $this->genSign($data,array_keys($data));
		if( $sign == $originSign ){
			return true;
		}
		else{
			return false;
		}
	}
}