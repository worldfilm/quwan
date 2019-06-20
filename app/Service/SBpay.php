<?php
namespace App\Service;

/**
 * 扫呗支付
 */
class SBpay{

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
	private $gateway = 'http://api.sao8pay.com/online/gateway';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/sbpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = 'f7619e5e53fe90ed2ddbb0f8462c73a0';
		$this->merchantId = '18130';
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
			\Log::error("[扫呗支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'WEIXINWAP';
                break;
            case 'wechat_qr':
                $service = 'WEIXIN';
                break;
            case 'alipay_wap':
                $service = 'ALIPAYWAP';
                break;
            case 'alipay_qr':
                $service = 'ALIPAY';
                break;
            case 'qq_wap':
                $service = 'QQWAP';
                break;
            case 'qq_qr':
                $service = 'QQ';
                break;
            case 'jd_wap':
                $service = 'JDWAP';
                break;
            case 'jd_qr':
                $service = 'JD';
                break;
			default:
				\Log::error("[扫呗支付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
            'version'       => "3.0",
            'method'        => "YF.online.interface",
            'partner'       => $this->merchantId,
            'banktype'      => $service,
            'paymoney'      => $orderInfo['amount'],
            'ordernumber'   => $orderInfo['out_trade_no'],
            'callbackurl'   => iconv("GBK","UTF-8", $this->notify_url),
            'hrefbackurl'   => $this->return_url,
            'attach'        => "mac",
            'isshow'        => "1"
		];

        $signParam['sign'] = $this->genSign($signParam);
        return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
    public function genSign($data){
        $signSource = sprintf("version=%s&method=%s&partner=%s&banktype=%s&paymoney=%s&ordernumber=%s&callbackurl=%s%s",
            $data['version'],
            $data['method'],
            $data['partner'],
            $data['banktype'],
            $data['paymoney'],
            $data['ordernumber'],
            $data['callbackurl'],
            $this->signKey);
        return md5($signSource);
    }

    /**
     * 验签
     * @param $param
     * @return bool
     */
	public function checkNotifySign($param){
        $signSource = sprintf("partner=%s&ordernumber=%s&orderstatus=%s&paymoney=%s%s",
            $param['partner'],
            $param['ordernumber'],
            $param['orderstatus'],
            $param['paymoney'],
            $this->signKey);
        if( $param['sign'] == md5($signSource) ){
            return true;
        } else {
            \Log::info("扫呗验签失败,返回签名:".$param['sign'].',生成签名:'.md5($signSource));
            return false;
        }
	}
}