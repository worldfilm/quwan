<?php
namespace App\Service;
use QrCode;

/**
 * 乐付支付【支付宝，微信】
 */
class Lepay{

	private  $alipay = [
		'merchantId'=>'100805',
		'md5'=>'d9a834902b4ce04d0652746c8a45bbe8',
		'sign_type'=>'MD5',
		'pay_type' =>'ali_pay_type',
	];

	private  $wechat = [
		'merchantId'=>'100804',
		'md5'=>'1328c31a4beeb3bf63e5b4abbec3bc09',
		'sign_type'=>'MD5',
		'pay_type' =>'wx_pay_type',
	];

	private $notify_url = '';

	private $gateway    = 'http://service.lepayle.com/api/md5/gateway';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/baoteewx";
	}

	public function createOrder($type, $method ,$orderInfo,$isJump=1){
		if( $method == 'wap' ){
			$payBody = $this->createWapOrder($type, $orderInfo);
			if(!$payBody){
				return false;
			}
			if($isJump){
				$payHtmlUri = $payBody;
			}else{
				$payHtmlUri = $this->genPayHtml($payBody, $orderInfo);
			}

			return [
				'type'	=>	'wap',
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[lePay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo){

		//选择支付方式
		switch ($type) {
			case 'wechat':
				$service = 'wx_pay';
				$config  = $this->wechat;
				$payType = 'wx_sm';
				break;
			case 'alipay':
				$service = 'ali_pay';
				$config  = $this->alipay;
				$payType = 'ali_sm';
				break;
			default:
				\Log::error("[lePay] type error {$type}");
				return false;
				break;
		}

		//签名准备
		$content = [
			'out_trade_no'=>$orderInfo['out_trade_no'],
			'amount_str'=>$orderInfo['amount'],
			$config['pay_type']=>$payType,
			'subject'=>'1',
			'sub_body'=>'2',
			'remark'=>$service,
			'return_url'=>$this->notify_url
		];
		ksort($content);

		//获取加密信息
		$sign = $this->genSign($content,$config['md5']);
		//请求参数准备
		$param = [
			'partner'	=>	$config['merchantId'],
			'input_charset'=>'UTF-8',
			'service' => $service,
			'sign_type' => $config['sign_type'],
			'content' => URLEncode(http_build_query($content)),
			'sign'=>$sign,
			'request_time'=>date('YmdHis')
		];

		//请求
		$client = new \GuzzleHttp\Client();
		$response = $client->request('POST', $this->gateway,['form_params'=>$param]);
		$result = json_decode($response->getBody(),true);

		//返回结果判断
		if($result['is_succ'] == 'T'){
			$url = json_decode($result['result_json']);
			return $url->wx_pay_sm_url;
		}
		else{
			\Log::error("[lePay]\client error\t{$result['result_msg']}\t{$result['result_code']}");
			return false;
		}
	}

	//加密
	public function genSign($param, $signKey){
		ksort($param);
		$signStr = http_build_query($param);
		return md5($signStr . '&'.$signKey);
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
	public function checkNotifySign($data,$pay_type){
		$originSign = $data['sign'];
		unset($data['sign']);
		$sign = $this->genSign($data,$this->$pay_type['md5']);
		if( $sign == $originSign ){
			return true;
		}
		else{
			return false;
		}
	}
}