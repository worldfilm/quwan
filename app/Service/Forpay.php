<?php
namespace App\Service;
use QrCode;

/**
 * 付联卡支付
 */
class Forpay{

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
	 * 同步通知地址
	 * @var string
	 */
	private $success_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway    = 'http://api.fulianka.com/service.html';

	public function __construct(){
		$this->notify_url      = "http://".env("APP_DOMAIN")."/api/pay/notify/forpay";
		$this->success_url     = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId      = '20170916100001223236'; //test:'test001';
		$this->signKey 		   = '000000005e84aa37015e866811e70002';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo,$method);
			$payHtmlUri = $payBody;
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[Forpay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$payChannel = 'WEIXIN';
				$payType    = 'SCAN_QR';
				break;
			case 'alipay_qr':
				$payChannel = 'ALIPAY';
				$payType  = 'SCAN_QR';
				break;
			case 'wechat_wap':
				$payChannel = 'WEIXIN';
				$payType  = 'H5';
				break;
			case 'alipay_wap':
				$payChannel = 'ALIPAY';
				$payType  = 'H5';
				break;
			default:
				\Log::error("[Forpay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$param = [
			'serviceCode'     => 'ONLINE_PAY',
			'merchantOrderNo' => $orderInfo['out_trade_no'],
			'signType' 		  => 'MD5',
			'partnerId'	      => $this->merchantId,
			'notifyUrl'   =>  iconv("GBK","UTF-8", $this->notify_url),
			'clientIp'    =>  rand(100,255).'.'.rand(50,150).'.'.rand(100,255).'.'.rand(1,200),
			'amount'      =>  $orderInfo['amount'],
			'payType'	  =>  $payType,
			'payChannel'  =>  $payChannel,
		];

		if($payType == 'H5'){
			$param['imei'] = 'DWAH'.rand(1,100).'JKADAD'.rand(99,255).'KAWHKDHKA';
		}

		\Log::info('[Forpay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$this->signKey);
		//请求参数准备
		$param['sign'] = $sign;

		//请求
		$client = new \GuzzleHttp\Client();
		$response = $client->request('POST', $this->gateway,['form_params'=>$param]);
		$result = json_decode($response->getBody(),true);

		if(is_array($result)){
			if($result['returnCode'] == 'SUCCESS'){
				return ( $method == 'wap' ) ? $result['payUrl'] : $result['payParam']['code_img_url'];
			}
			\Log::info('[Forpay] request error'.json_encode($result));
			return false;
		}
		\Log::info('[Forpay] request error '.$response->getBody());
		return false;
	}

	//加密
	public function genSign($param, $signKey){
		//$param['partnerId'] = $this->merchantId;
		$mstr = self::paramsFilter($param);
		$signStr = self::createLinkString($mstr);

		return md5($signStr.$signKey);
	}

	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $params array 需要拼接的数组
	 * @return string 拼接完成以后的字符串
	 */
	public static function createLinkString($params) {
		$strParams = "";
		foreach ($params as $key => $val) {
			if(is_array($val)){
				$val = json_encode($val, JSON_UNESCAPED_UNICODE);
			}else if(is_bool($val)){
				if($val) $val="true"; else $val="false";
			}
			$strParams .= "$key=" . ($val) . "&";
		}
		$strParams = substr($strParams, 0, -1);

		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$strParams = stripslashes($strParams);}

		return urldecode($strParams);
	}

	/**
	 * 除去数组中的空值和签名参数
	 * @param $params array 签名参数组
	 * @return array 去掉空值与签名参数后的新签名参数组
	 */
	public static function paramsFilter($params) {
		$para_filter = array();
		while (list ($key, $val) = each ($params)) {
			if($key == "sign" || static::checkEmpty($val)) continue;
			else	$para_filter[$key] = urlencode($params[$key]);
		}
		ksort($para_filter);
		reset($para_filter);
		return $para_filter;
	}

	/**
	 * 校验$value是否非空
	 *  if not set ,return true;
	 *    if is null , return true;
	 * @param string $value
	 * @return bool
	 */
	public static function  checkEmpty($value) {
		if (!isset($value))
			return true;
		if ($value === null)
			return true;
		if (is_string($value) && trim($value) === "")
			return true;

		return false;
	}

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($data){
		$originSign = $data['sign'];
		unset($data['sign']);
		$callbackSign = $this->genSign($data,$this->signKey);

		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 验签：将返回结果（json-string或数组）进行签名验证
	 * 验签成功返回true，失败则返回false
	 *
	 * @param $resp string|array 待验证json-string或数组
	 * @param $key  string 分配的商户key
	 * @return bool
	 */
	public static function verify($resp, $key){

		if(static::checkEmpty($resp)) die("验签输入参数不能为空");

		if(is_string($resp)){
			$resp = static::_json_decode($resp);
		}

		//服务端验签失败响应结果不存在sign参数
		if(!array_key_exists("sign",$resp)) return false;

		$sign = $resp['sign'];
		$preStr = static::getPreSignStr($resp);
		$mySign = static::sign($preStr, $key, $resp["signType"]);

		return $mySign === $sign;
	}
}