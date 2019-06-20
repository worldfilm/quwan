<?php
namespace App\Service;

/**
 * 速通支付
 */
class STpay{

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
	 * 请求网关
	 * @var string
	 */
	private $gateway = 'http://gate.sutongpay.com/chargebank.aspx';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/stpay";
		$this->signKey = '526476cf65064853ba09d7d336b14ce9';
		$this->merchantId ='1375';
	}

	public function createOrder($payMethod, $method, $orderInfo, $isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo, $method);
			$payHtmlUri = $payBody;
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[速通支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$service = 1004;
				break;
			case 'alipay_qr':
				$service = 992;
				break;
			case 'wechat_wap':
				$service = 2099;
				break;
			case 'alipay_wap':
				$service = 2098;
				break;
            case 'bank_wap':
                $service = 2097;
                break;
            case 'qq_wap':
                $service = 2101;
                break;
			default:
				\Log::error("[速通支付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'parter'     => $this->merchantId,
			'type'       => $service,
			'value'      => $orderInfo['amount'],
			'orderid'    => $orderInfo['out_trade_no'],
			'callbackurl'=> iconv("GBK","UTF-8", $this->notify_url)
		];

        $signParam['sign'] = $this->genSign($signParam);

		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
	public function genSign($params){
        $str = "parter=".$params['parter'].'&type='.$params['type'].'&value='.$params['value'].
            '&orderid='.$params['orderid'].'&callbackurl='.$params['callbackurl'].$this->signKey;
        $post_key = md5($str);
        return $post_key;
	}

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($param){
        $tmp = "orderid=".$param['orderid'].'&opstate='.$param['opstate'].'&ovalue='.$param['ovalue'].$this->signKey;
        $getSign = md5($tmp);
        if( $param['sign'] == $getSign ){
            return true;
        } else {
            return false;
        }
	}
}