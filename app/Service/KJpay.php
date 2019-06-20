<?php
namespace App\Service;

/**
 * 快接支付
 */
class KJpay{

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
	private $gateway = 'http://api.kj-pay.com/alipay/wap_pay';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/kjpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = 'a9ae096660cc338d95292c46af175372';
		$this->merchantId = '2018815650';
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
			\Log::error("[快接支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
//                $service = 'ALIPAYWAP';
                break;
			default:
				\Log::error("[快接支付] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $signParam = [
            'merchant_no'       => $this->merchantId,
            'merchant_order_no' => $orderInfo['out_trade_no'],
            'notify_url'        => iconv("GBK","UTF-8", $this->notify_url),
            'start_time'        => date('YmdHis'),
            'trade_amount'      => $orderInfo['amount'],
            'goods_name'        => "mac",
            'goods_desc'        => "book",
            'user_ip'           => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200),
            'pay_sence'         => '{"type":"Wap","wap_url":"https://www.kk30.com","wap_name":"快快网络"}',
            'sign_type'         => 1
        ];

        $signParam['sign'] = $this->genSign($signParam);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $signParam);
        $content = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($content,true);
        \Log::error("快接支付wap返回:".$content.",打开支付宝链接:".$result['data']['pay_url']);
        return $result['data']['pay_url'];
	}

	//加密
    public function genSign($data){
        $para_filter = array();
        while (list ($key, $val) = each ($data)) {
            if($key == "sign" || $val == "")continue;
            else $para_filter[$key] = $data[$key];
        }
        ksort($para_filter);
        reset($para_filter);
        $str = urldecode(http_build_query($para_filter));
        return md5($str."&key=".$this->signKey);
    }

    /**
     * 验签
     * @param $param
     * @return bool
     */
	public function checkNotifySign($param){
        $signStr = $this->genSign($param);
        if( $param['sign'] == $signStr ){
            return true;
        } else {
            \Log::info("快接支付验签失败,返回签名:".$param['sign'].',生成签名:'.$signStr);
            return false;
        }
	}
}