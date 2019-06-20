<?php
namespace App\Service;
use Spatie\ArrayToXml\ArrayToXml;
use QrCode;

class SPpay{

	const SERVICE_WECHAT_QR  = 'pay.weixin.native',
		  SERVICE_ALIPAY_QR  = 'pay.alipay.native';

	function __construct(){
		$this->config = config('sppay');
	}

	public function createOrder($type, $method, $orderInfo){
		if( $method == 'qr' ){
			$hidUrl = $this->createQrOrder($type, $orderInfo);
			$qrUri = $this->genQrCode($hidUrl, $orderInfo);
			return [
				'type'	=>	'qr',
				'qr_img'	=>	$qrUri,
			];
		}
		else{
			\Log::error("[SPpay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createQrOrder($type, $orderInfo){
		if( $type == 'alipay' ){
			$service = self::SERVICE_ALIPAY_QR;
		}
		elseif( $type == 'wechat' ){
			$service = self::SERVICE_WECHAT_QR;
		}
		else{
			\Log::error("[SPpay] type error {$type}");
		}
		$param  = [
			'service'	=>	$service,
			'charset'	=>	'UTF-8',
			'mch_id'	=>	$this->config['partner_id'],
			'out_trade_no'	=>	$orderInfo['out_trade_no'],
			'body'	=>	$orderInfo['title'],
			'total_fee'	=>	$orderInfo['amount'] * 100,
			'mch_create_ip'	=>	$orderInfo['payerIp'],
			'notify_url'	=>	$this->config['notify_url'],
			'nonce_str'	=>	md5(time() . uniqid(true)),
		];
		// dd($param);
		$param['sign'] = $this->genSign($param);

		$result = ArrayToXml::convert($param);

		$client = new \GuzzleHttp\Client();

		$response = $client->request('POST', $this->config['gateway'], [
		    'body' => $result
		]);
		$body = $response->getBody();
		\Log::info("SPpay" . (string)$body . json_encode($this->config));
		$parser = new \XML2Array\Parser($body);
		$data = $parser->toArray();

		return $data['code_url'];
	}

	public function genSign($param){
		ksort($param);
		foreach ($param as $key => $value) {
			if(empty($value) && $value !== '0'){
				unset($param[$key]);
			}
		}
		$signStr = urldecode( http_build_query($param) ) . '&key=' . $this->config['key'];
		// dd($signStr);
		// echo $signStr;
		return strtoupper(md5($signStr));
	}

	public function genXML($param){
		$xml = new \SimpleXMLElement('<root/>');
		array_walk_recursive($param, array ($xml, 'addChild'));
		return $xml->asXML();
	}

	public function genQrCode($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.png';
		$uriPath = "/qrcodes/{$filename}";
		$path = public_path($uriPath);
		QrCode::format('png')->size(288)->margin(0)->generate($data, $path);
		return $uriPath;
	}

	public function checkNotifySign($data){
		$originSign = $data['sign'];
		unset($data['sign']);
		$sign = $this->genSign($data);
		if( $originSign == $sign ){
			return true;
		}
		else{
			\Log::info("SPpay sign error want{$sign}\t got {$originSign}");
			return false;
		}
	}

}