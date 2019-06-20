<?php
namespace App\Service;
use QrCode;

class Baoteewxpay{
	private $config = null;
	function __construct(){
		$this->config = config('baoteewxpay');
	}

	public function createOrder($type, $method ,$orderInfo){
		if( $method == 'wap' ){
			$payBody = $this->createWapOrder($type, $orderInfo);
			if(!$payBody){
				return false;
			}
			$payHtmlUri = $this->genPayHtml($payBody, $orderInfo);
			return [
				'type'	=>	'wap',
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[Baoteewxpay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo){
		if( $type == 'wechat' ){
			$payType = 'wechat';
		}
		else{
			\Log::error("[Baoteewxpay] type error {$type}");
			return false;
		}
		$param = [
			'area'	=>	$this->config['area'],
			'area_notify_url'	=>	$this->config['notify_url'],
			'area_out_trade_no'	=>	$orderInfo['out_trade_no'],
			'body'	=>	'onlinepay',
			'total_fee'	=>	$orderInfo['amount']*100,
			'app_pay'	=>	'Y',
		];
		// dd($param);
		$sign = $this->genSign($param,['area','area_out_trade_no','body','total_fee']);
		$param['sign'] = $sign;
		// dd($param);
		$url = $this->config['gateway'];
		$client = new \GuzzleHttp\Client();
		$payUrl = $url . '?' . urldecode(http_build_query($param));
		$response = $client->request('GET', $payUrl, [
			'allow_redirects'	=>	false,
		]);
		$location = $response->getHeader("Location")[0];

		// $body = (string)$response->getBody();
		$body =  "<script>location.href=\"". $location . "\"</script>";
		return $body;
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