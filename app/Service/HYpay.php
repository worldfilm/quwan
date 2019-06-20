<?php
namespace App\Service;

/**
 * 虎云支付
 */
class HYpay{

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
	private $gateway = 'http://www.39team.com/apisubmit';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/hypay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = '6598a6ab06f340c928b67b7b23e4461174b5c796';
		$this->merchantId = '10965';
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
			\Log::error("[虎云]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'wxwap';
                break;
			case 'wechat_qr':
				$service = 'weixin';
				break;
            case 'alipay_wap':
                $service = 'alipaywap';
                break;
            case 'alipay_qr':
                $service = 'alipayscan';
                break;
			default:
				\Log::error("[虎云] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
            'version'   => "1.0",
            'customerid'=> $this->merchantId,
            'userid'    => rand(10000,99999),
            'total_fee' => number_format($orderInfo['amount'],2,'.',''),
            'sdorderno' => $orderInfo['out_trade_no'],
            'notifyurl' => iconv("GBK","UTF-8", $this->notify_url),
            'returnurl' => $this->return_url,
            'paytype'   => $service,
            'remark'    => "mac",
            'bankcode'  => ""
		];
        $signParam['sign'] = $this->genSign($signParam);
		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

    /**
     * MD5加密
     * @param $param
     * @return string
     */
    public function genSign($param){
        $str = 'version='.$param['version'].'&customerid='.$param['customerid'].'&userid='.$param['userid'].'&total_fee='.$param['total_fee'].
            '&sdorderno='.$param['sdorderno'].'&notifyurl='.$param['notifyurl'].'&returnurl='.$param['returnurl']."&".$this->signKey;
        $post_key = md5($str);
        return $post_key;
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
            \Log::error("验签失败--->生成MD5:".$getSign.PHP_EOL."验签字符串:".$tmp);
            return false;
        }
	}
}