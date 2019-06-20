<?php
namespace App\Service;

/**
 * 天盛支付
 */
class TSPay{

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
	private $gateway = 'http://pay.txpays.com';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/tspay";
		$this->signKey = 'd53d09d2a38d9dee55ea67baaa691d94';
		$this->merchantId = '100017';
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
			\Log::error("[天盛支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = 'ALIPAY_WAP';
                break;
            case 'wechat_wap':
                $service = 'WECHAT_WAP';
                break;
            case 'qq_wap':
                $service = 'QQ_WAP';
                break;
            case 'jd_wap':
                $service = 'JD_WAP';
                break;
			default:
				\Log::error("[天盛支付] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $data = [
            'MerchantCode'=> $this->merchantId,
            'BankCode'    => $service,
            'Amount'      => number_format($orderInfo['amount'],2,'.',''),
            'OrderId'     => $orderInfo['out_trade_no'],
            'NotifyUrl'   => iconv("GBK","UTF-8", $this->notify_url),
            'ReturnUrl'   => "",
            'OrderDate'   => time(),
            'Remark'      => "v"
        ];
        $signText = 'MerchantCode=['.$data['MerchantCode'].']OrderId=['.$data['OrderId'].']Amount=['.$data['Amount'].']NotifyUrl=['.
            $data['NotifyUrl'].']OrderDate=['.$data['OrderDate'].']BankCode=['.$data['BankCode'].']TokenKey=['.$this->signKey.']';
        $data['Sign'] = strtoupper(md5($signText));
        $sHtml = "<form name='h5PaySubmit' action='".$this->gateway."' method='post'>";
        foreach ($data as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['h5PaySubmit'].submit();</script>";
        return $sHtml;
        // 文档上要求post请求,经测试,get请求亦可！
//        return $payUrl = $this->gateway . '?' . http_build_query($data);
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $signText = 'MerchantCode=['.$param['MerchantCode'].']OrderId=['.$param['OrderId'].']OutTradeNo=['.$param['OutTradeNo'].']Amount=['.
            $param['Amount'].']OrderDate=['.$param['OrderDate'].']BankCode=['.$param['BankCode'].']Remark=['.$param['Remark'].']Status=['.
            $param['Status'].']Time=['.$param['Time'].']TokenKey=['.$this->signKey.']';
        $getSign = strtoupper(md5($signText));
        if( strcasecmp($getSign, $param['Sign']) == 0 ){
            return true;
        } else {
            \Log::error("验签失败,回调参数:".json_encode($param,320).",验签字符串:".$signText.",生成MD5:".$getSign);
            return false;
        }
	}
}