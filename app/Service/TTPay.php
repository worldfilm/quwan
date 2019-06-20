<?php
namespace App\Service;

/**
 * TT支付
 */
class TTPay{

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
	private $gateway = 'http://pay.tt-xs.cn/getway/pay';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/ttpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/rechargeH5";
		$this->signKey = '77b575dc74cb388f20e5453523052673a643ecbf';
		$this->merchantId = '10067';
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
			\Log::error("[TT支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = 'aliwap';
                break;
            case 'wechat_wap':
                $service = 'wxwap';
                break;
			default:
				\Log::error("[TT支付] type error {$payMethod}");
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
            'bank_code' => "",
            'notifyurl' => iconv("GBK","UTF-8", $this->notify_url),
            'returnurl' => $this->return_url,
            'remark'    => "mac"
        ];

        $signParam['sign'] = $this->genSign($signParam);

        \Log::error("TT支付提交参数:".json_encode($signParam,320));
        return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

    /**
     * MD5加密
     * @param $data
     * @return string
     */
    public function genSign($data){
        $str = "version=$data[version]&customerid=$data[customerid]&total_fee=$data[total_fee]&sdorderno=$data[sdorderno]&notifyurl=$data[notifyurl]&returnurl=$data[returnurl]&".$this->signKey;
        $sign = md5($str);
        return $sign;
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $tmp = "customerid=".$param['customerid'].'&status='.$param['status'].'&sdpayno='.$param['sdpayno'].'&sdorderno='.$param['sdorderno'].
            '&total_fee='.$param['total_fee'].'&paytype='.$param['paytype'].'&'.$this->signKey;
        $getSign = md5($tmp);
        if( $param['sign'] == $getSign ){
            return true;
        } else {
            \Log::info("TT支付验签失败,返回签名:".$param['sign'].',生成签名:'.$getSign);
            return false;
        }
	}
}