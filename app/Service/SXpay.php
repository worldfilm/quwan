<?php
namespace App\Service;

/**
 * 盛祥支付
 */
class SXpay{

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
    private $gateway = 'http://api.shxpac.com/pay.aspx';

    public function __construct(){
        $this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/sxpay";
        $this->signKey = '6049c957e7104cd8a20577195c4c1421';
        $this->merchantId = '3447';
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
			\Log::error("[盛祥]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_qr':
                $service = "2001";
                break;
            case 'alipay_qr':
                $service = "2003";
                break;
            case 'wechat_wap':
                $service = "2005";
                break;
            case 'alipay_wap':
                $service = "2007";
                break;
			default:
				\Log::error("[盛祥] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $signParam = [
            'userid'    => $this->merchantId,
            'orderid'   => $orderInfo['out_trade_no'],
            'money'     => $orderInfo['amount'],
            'url'       => iconv("GBK","UTF-8", $this->notify_url),
            'bankid'    => $service,
            'ext'       => "mac",
        ];

        \Log::info('[SXpay] sign array'.json_encode($signParam,320));

        $sign = "userid=".$signParam['userid']."&orderid=".$signParam['orderid']."&bankid=".$signParam['bankid']."&keyvalue=".$this->signKey;
        $sign2 = "money=".$signParam['money']."&userid=".$signParam['userid']."&orderid=".$signParam['orderid']."&bankid=".$signParam['bankid']."&keyvalue=".$this->signKey;
        $sign = strtolower(md5($sign));//签名数据 32位小写的组合加密验证串
        $sign2 = strtolower(md5($sign2));//签名数据2 32位小写的组合加密验证串

        return $payUrl = $this->gateway."?userid=".$signParam['userid']."&orderid=".$signParam['orderid']."&money=".$signParam['money']
            ."&url=".$signParam['url']."&bankid=".$signParam['bankid']."&sign=".$sign."&ext=".$signParam['ext']."&sign2=".$sign2;
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $localsign = "returncode=".$param['returncode']."&userid=".$param['userid']."&orderid=".$param['orderid']."&keyvalue=".$this->signKey;
        $localsign2 = "money=".$param['money']."&returncode=".$param['returncode']."&userid=".$param['userid']."&orderid=".$param['orderid']."&keyvalue=".$this->signKey;
        $localsign = strtolower(md5($localsign));
        $localsign2 = strtolower(md5($localsign2));
        if($localsign == $param['sign'] && $localsign2 == $param['sign2']){
            return true;
        }else{
            return false;
        }
	}
}