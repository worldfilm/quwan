<?php
namespace App\Service;
use QrCode;

/**
 * 博士支付
 */
class Doctorpay{

	/**
	 * md5秘钥
	 * @var string
	 */
	private  $md5SignKey = 'sJK2dTfB';

	/**
	 * 参数秘钥
	 * @var string
	 */
	private  $desSignKey = '9f7rjo4Y';

	/**
	 * 管理面膜
	 * @var string
	 */
	private  $managePassword = '123123';

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
	private $gateway    = 'http://api11.com/api/pay';

	public function __construct(){
		$this->notify_url      = "http://".env("APP_DOMAIN")."/api/pay/notify/doctorpay";
		$this->success_url     = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId      = 'ys123'; //test:'test001';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payHtmlUri = $this->createWapOrder($payMethod, $orderInfo,$method);
			if(!$payHtmlUri){
				return false;
			}

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[doctorPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_wap':
				$payType  = 'WAY_TYPE_WEBCAT_PHONE';
				break;
			case 'alipay_wap':
				$payType  = 'WAY_TYPE_ALIPAY_PHONE';
				break;
			case 'qq_wap':
				$payType  = 'WAY_TYPE_QQ';
				break;
			default:
				\Log::error("[doctorPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$param = [
			'pname'     => $this->merchantId,
			'ptype'	    => $payType,
			'poid'      => $orderInfo['out_trade_no'],
			'pmoney'    => $orderInfo['amount'],
			'pbank'		=> '',
			'purl'	    =>  $this->notify_url,
			'payType'   => $payType,
			'premarks'  => 'wechat',
			'psyspwd'   => md5($this->managePassword.$this->md5SignKey)
		];

		\Log::info('[doctorPay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$this->desSignKey);

		return $this->gateway.'?params='.$sign.'&uname='.$this->merchantId;
	}

	//加密
	public function genSign($params, $deskey){
		$sign = '';
		foreach ($params as $key => $value) {
			$sign .= $key.'='.$value.'!';
		}

		$sign = substr($sign,0,strlen($sign)-1);

		$size = @mcrypt_get_block_size('des', 'cbc');
        $input = $this->pkcs5Pad($sign, $size);
        $td = @mcrypt_module_open('des', '', 'cbc', '');

        @mcrypt_generic_init($td, $deskey, $deskey);
        $data = mcrypt_generic($td, $input);
        @mcrypt_generic_deinit($td);
        @mcrypt_module_close($td);
        $data = base64_encode($data);

        return preg_replace("/\s*/", '',$data);
	}

	public function pkcs5Pad($text, $blocksize) {
		$pad = $blocksize - (strlen ( $text ) % $blocksize);
		return $text . str_repeat ( chr ( $pad ), $pad );
	}


	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($data){
		$sign = $data['syspwd'];
		$callbackSign = md5($this->managePassword.$this->md5SignKey);
		if( strcasecmp($callbackSign,$sign) == 0 ){
			return true;
		}else{
			return false;
		}
	}

}