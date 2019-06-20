<?php
namespace App\Service;

/**
 * 众兴通支付
 */
class Zxtpay{

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
	private $gateway = 'http://zf.xbyy168.com/apisubmit';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/zxtpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = '226c578b0d2fe41d7876bd214c67174f6949cbc8';
		$this->merchantId = '11082';
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
			\Log::error("[众兴通支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'wxh5';
                break;
            case 'alipay_wap':
                $service = 'alipaywap';
                break;
            case 'qq_wap':
                $service = 'qqwallet';
                break;
            case 'bank_web':
                $service = 'bank';
                break;
			case 'wechat_qr':
				$service = 'weixin';
				break;
			case 'alipay_qr':
				$service = 'alipay';
				break;
            case 'qq_qr':
                $service = 'qqrcode';
                break;
			default:
				\Log::error("[众兴通支付] type error {$payMethod}");
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
			'bankcode'  => "",
			'notifyurl' => iconv("GBK","UTF-8", $this->notify_url),
			'returnurl' => $this->return_url
		];

        $signParam['sign'] = $this->genSign($signParam);

		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
	public function genSign($params){
        $str = "version=".$params['version'].'&customerid='.$params['customerid'].'&total_fee='.$params['total_fee'].
            '&sdorderno='.$params['sdorderno'].'&notifyurl='.$params['notifyurl'].'&returnurl='.$params['returnurl']."&".$this->signKey;
        $post_key = strtolower(md5($str));
        return $post_key;
	}

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($param){
        $tmp = "customerid=".$param['customerid'].'&status='.$param['status'].'&sdpayno='.$param['sdpayno'].
            '&sdorderno='.$param['sdorderno'].'&total_fee='.$param['total_fee'].'&paytype='.$param['paytype'].'&'.$this->signKey;
        $getSign = strtolower(md5($tmp));
        if( $param['sign'] == $getSign ){
            return true;
        } else {
            return false;
        }
	}
}