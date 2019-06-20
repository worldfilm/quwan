<?php
namespace App\Service;
use QrCode;

/**
 * 智通宝支付
 */
class ZtBaopay{

	/**
	 * 智通宝公钥
	 */
// 	const rsaSignKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrrWw+sYGqOiPXzshHtfNIVGqV
// GNBACQuCQNV+9BgjrY7L5Jp813wed8cmlXTdlGADvJqNBlfvu+nclQPRvFTN2OWYDx+L2bP977rhbdN2QvIL2p
// +e77al9hmH8J+oI8cbwDsfHCXdpXBBmQ2EIHmvDaFAf5uO8dZpLKPKFy3QSwIDAQAB';

	const rsaSignKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8GWJYKAH6hDCjLbxOY6o0VZq1L18X6xLbNYOubGATeZSeU/BdYqxjfRqHYqfwPPXciKLfmIk0zlFiJ8DPbDzyoMJuxcMFULvqvjv6dFfWI6qB44JeEGuxwZWKbpB2Rx0qrRjwKyVZVOCNmRXUuDVHC6E2h53/SirJAMrQRy6rGwIDAQAB';
	/**
	 * 商户私钥
	 */
	const merchantPrivateKey ='MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAL3GbztpsE7aKJ/r
2W7XSmMvX/+ILlzpPXLz+ySOHhwLQN7EP+XGf4C1qf6zeR3GaYcWVQtCdK5GlhF8
9ldlvcDT2AIwRJ8n35k/NxMQcoXPqT+4W99gd8RYZpsOCrSoWxMStNFln7dX+5oO
GY3lMsjzMdS6p0G7L7nCsdWfXfYdAgMBAAECgYEAtVt7VUXtTY3CoQHdKsXnut29
WhAkbnofVUnASfDe6WH/vmPBxK5rju8M4/FT35aLpqM65qW9qAagx1mqeV8rdAHf
vYr6UJc63Abd8W+0zB1n/14x1yaJeZu5rEcC0LZ9YPs5YTnuY2GGqN9Gi7C3s5rt
LjRtnysvly7idZxQV2kCQQD06Yo2TJW5YM9nOfzZqusaDuhN1TDON7+E8GtR+7c9
Pjzvb3XlZCkW3eA9cKyw7z/mYtY+ToVtPRDNt9RTO54TAkEAxl3fKIatCihZXXQn
g4Je+2aKobXnQ2qA80PtNCUQQ73SFrRY9hfXiQ5QeOt6OgvH0sE018bH5LEUIyqx
fWvhDwJBAJMcbR7spcryceKpnE4LMqk2ZyfJdUWJiwsJdw2Jy6mH5wZTx1eA8IWB
xR5ivfiR3ao/mD1Y4SCa26sWTv2oA+0CQQCzi83sSZIgSoswqm0FfKBqHtNGMXaE
r8bN6WKvOwjwt8SL5mtLPCNLm5g9Cq6UEYupeFVTJUpS9fDLL959LoMDAkBEA2DF
WSdWUiqP+DU7D7w4fRTHxKTiV97Bs0EL6OYyA8hBw62IeMs2jvMjJiP7XmiAUt93
ZGHAy9FLT7t+jZ8w';


	/**
	 * 商户公钥
	 */
	const publicKey ='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC9xm87abBO2iif69lu10pjL1//
iC5c6T1y8/skjh4cC0DexD/lxn+Atan+s3kdxmmHFlULQnSuRpYRfPZXZb3A09gC
MESfJ9+ZPzcTEHKFz6k/uFvfYHfEWGabDgq0qFsTErTRZZ+3V/uaDhmN5TLI8zHU
uqdBuy+5wrHVn132HQIDAQAB';


	/**
	 * 商户号
	 * @var string
	 */
	const merchantId = '100888008067';

	/**
	 * 异步通知地址
	 * @var string
	 */
	private $notify_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	// private $gateway    = 'https://pay.ztbaofu.com/gateway?input_charset=UTF-8';
	private $gateway    = 'https://api.ztbaofu.com/gateway/api/h5apipay';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/ztbaopay";
	}

	public function createOrder($type, $method, $orderInfo,$isJump=1){

		if( $method ){
			$payBody = $this->createWapOrder($type, $orderInfo);
			if(!$payBody){
				return false;
			}

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payBody
			];
		}
		else{
			\Log::error("[juxinPay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo){

		//选择支付方式
		switch ($type) {
			case 'wechat':
				$payType = 'weixin_h5api';
				break;
			case 'alipay':
				$payType = 'alipay_h5api';
				break;
			case 'qq':
				$payType = 'qq_h5api';
				break;
			case 'jd':
				$payType = 'jd_h5api';
				break;
			default:
				\Log::error("[ZtbaoPay] type error {$type}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
		    'interface_version' =>'V3.1',
			'merchant_code' => self::merchantId,
			'order_no'  => $orderInfo['out_trade_no'],
			'service_type' => $payType,
			'notify_url' => $this->notify_url,
			'input_charset' => 'UTF-8',
			'order_amount' => $orderInfo['amount'],
			'order_time' => date('Y-m-d H:i:s'),
			'product_name'=> $type,
			'client_ip'=>rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200),
		];

		\Log::info('Ztbao sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,self::rsaSignKey);


		//请求参数准备
		$signParam['sign'] = $sign;
		$signParam['sign_type'] = 'RSA-S';

		//请求
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->gateway);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signParam));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        $result = simplexml_load_string($response);
        \Log::info('Ztbao response'.json_encode($result));
        if($result->response->resp_code == 'SUCCESS' && isset($result->response->payURL)){
        	return urldecode($result->response->payURL);
        }else{
        	return false;
        }
	}

	//加密
	public function genSign($param, $signKey){
		ksort($param);
		$signStr = '';
		foreach ($param as $key => $value) {
			$signStr .= $key.'='.$value.'&';
		}
		$signStr =substr($signStr,0,strlen($signStr)-1);

		$privateKey = "-----BEGIN PRIVATE KEY-----"."\r\n".wordwrap(trim(self::merchantPrivateKey),64,"\r\n",true)."\r\n"."-----END PRIVATE KEY-----";

		openssl_sign($signStr,$sign_info,$privateKey,OPENSSL_ALGO_MD5);
		return base64_encode($sign_info);

	}

	/**
	 * 解密
	 * @param  [type] $sign [description]
	 * @return [type]       [description]
	 */
	public function decrySign($sign)
	{
		$publicKey = openssl_get_publickey(self::publicKey);
		$plainData = str_split(base64_decode($sign), 117);
		$encrypted = '';
		foreach ($plainData as $chunk) {
			$partialEncrypted = '';
			$encryptionOk = openssl_public_decrypt($chunk,$partialEncrypted,$publicKey,OPENSSL_PKCS1_PADDING);
			if($encryptionOk === false){
    			return false;
    		}
    		$encrypted .= $partialEncrypted;
		}
		return $encrypted;
	}

	/**
	 * 生成跳转自有页面
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

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($data){
		ksort($data);
		$sign = $data['sign'];
		unset($data['sign']);
		unset($data['sign_type']);

		$signStr = '';
		foreach ($data as $key => $value) {
			$signStr .= $key.'='.$value.'&';
		}
		$signStr =substr($signStr,0,strlen($signStr)-1);

		$dinpay_public_key = "-----BEGIN PUBLIC KEY-----"."\r\n".wordwrap(trim(self::rsaSignKey),62,"\r\n",true)."\r\n"."-----END PUBLIC KEY-----";
	    $dinpay_public_key = openssl_get_publickey($dinpay_public_key);

	    $flag = openssl_verify($signStr,base64_decode($sign),$dinpay_public_key,OPENSSL_ALGO_MD5);

		if( $flag > 0 ){
			return true;
		}else{
			return false;
		}
	}
}