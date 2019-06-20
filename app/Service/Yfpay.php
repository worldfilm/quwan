<?php
namespace App\Service;

/**
 * 银付
 */
class Yfpay{

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
     * 同步浏览器跳转
     * @var string
     */
	private $return_url = '';
	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway = 'http://api.all-linepay.com/pay.do';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/yfpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = 'w2fSzYxAk6eF8pVGuHMQCEPqis9NJomT';
		$this->merchantId = '1000018999';
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
			\Log::error("[银付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'WxH5';
                break;
            case 'qq_wap':
                $service = 'QQWap';
                break;
            case 'bank_web':
                $service = 'bank';
                break;
			case 'wechat_qr':
				$service = 'WxPay';
				break;
            case 'qq_qr':
                $service = 'QQPay';
                break;
			default:
				\Log::error("[银付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'notifyUrl'   => iconv("GBK","UTF-8", $this->notify_url),
			'returnUrl'   => $this->return_url,
			'payCode'     => $service,
			'merchantCode'=> $this->merchantId,
			'orderCode'   => $orderInfo['out_trade_no'],
			'orderTotal'  => number_format($orderInfo['amount'],2,'.',''),
			'orderTime'   => date('Y-m-d H:i:s'),
			'bankCode'    => "none"
		];
        $signParam['sign'] = $this->genSign($signParam);
		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

    /**
     * MD5加密
     * @param $params
     * @return string
     */
    public function genSign($params){
        $str = 'bankCode='.$params['bankCode'].'&merchantCode='.$params['merchantCode'].'&notifyUrl='.$params['notifyUrl'].'&orderCode='.$params['orderCode'].
            '&orderTime='.$params['orderTime'].'&orderTotal='.$params['orderTotal'].'&payCode='.$params['payCode'].'&returnUrl='.$params['returnUrl']."&key=".$this->signKey;
        $post_key = strtoupper(md5($str));
        return $post_key;
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $tmp = "fee=".$param['fee'].'&notifyStatus='.$param['notifyStatus'].'&outerCode='.$param['outerCode'].'&params='.$param['params'].
            '&result='.$param['result'].'&status='.$param['status'].'&tradeTime='.$param['tradeTime'].'&tradeTotal='.$param['tradeTotal'].
            '&transactionId='.$param['transactionId'].'&key='.$this->signKey;
        $getSign = strtoupper(md5($tmp));
        if( $param['sign'] == $getSign ){
            return true;
        } else {
            \Log::error("验签失败--->生成MD5:".$getSign.PHP_EOL."验签字符串:".$tmp);
            return false;
        }
	}
}