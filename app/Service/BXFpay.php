<?php
namespace App\Service;

/**
 * 百祥付支付
 */
class BXFpay{

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
	private $gateway = 'http://yonshi3.cn/apisubmit';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/bxfpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = 'a265257a95a988e1e38b8b6d64dc5e52a814aa8f';
		$this->merchantId = '11312';
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
			\Log::error("[百祥付支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'wxwap';
                break;
            case 'alipay_wap':
                $service = 'aliwap';
                break;
            case 'wechat_qr':
                $service = 'weixin';
                break;
            case 'alipay_qr':
                $service = 'alipay';
                break;
			default:
				\Log::error("[百祥付支付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
            'version'   => "1.0",
            'customerid'=> $this->merchantId,
            'sdorderno' => $orderInfo['out_trade_no'],
            'total_fee' => number_format($orderInfo['amount'],2,'.',''),
            'paytype'   => $service,
            'notifyurl' => iconv("GBK","UTF-8", $this->notify_url),
            'returnurl' => $this->return_url,
            'remark'    => "mac",
            'bankcode'  => "",
            'get_code'  => 0
		];

        $signParam['sign'] = $this->genSign($signParam);

		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
	public function genSign($params){
        $str = "version=".$params['version'].'&customerid='.$params['customerid'].'&total_fee='.$params['total_fee'].
            '&sdorderno='.$params['sdorderno'].'&notifyurl='.$params['notifyurl'].'&returnurl='.$params['returnurl']."&".$this->signKey;
        $post_key = md5($str);
        return $post_key;
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
	public function checkNotifySign($param){
        $tmp = "customerid=".$param['customerid'].'&status='.$param['status'].'&sdpayno='.$param['sdpayno'].
            '&sdorderno='.$param['sdorderno'].'&total_fee='.$param['total_fee'].'&paytype='.$param['paytype'].'&'.$this->signKey;
        $getSign = md5($tmp);
        if( $param['sign'] == $getSign ){
            return true;
        } else {
            return false;
        }
	}
}