<?php
namespace App\Service;

/**
 * 汇丰支付
 */
class HFPay{

	/**
	 * 秘钥
	 * @var string
	 */
	private $signKey = '';

	/**
	 * 商户号
	 * @var string
	 */
	private $merchantId = '';

	/**
	 * 异步通知地址
	 * @var string
	 */
	private $notify_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway = 'http://211.144.86.91/api/v3/cashier.php';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/hfpay";
		$this->signKey = 'dc78f1e05bd7307fd32134eaeed36221';
		$this->merchantId = 'SKB18091018588';
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
			\Log::error("[汇丰支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = 'aph5';
                break;
			default:
				\Log::error("[汇丰支付] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $signParam = [
            'merchant'  => $this->merchantId,
            'qrtype'    => $service,
            'customno'  => $orderInfo['out_trade_no'],
            'money'     => $orderInfo['amount'],
            'sendtime'  => time(),
            'notifyurl' => iconv("GBK","UTF-8", $this->notify_url),
            'backurl'   => "",
            'risklevel' => "",
        ];

        $signParam['sign'] = $this->genSign($signParam);

        \Log::error("汇丰支付提交参数:".json_encode($signParam,320));
        $sHtml = "<form name='h5PaySubmit' action='".$this->gateway."' method='post'>";
        foreach ($signParam as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['h5PaySubmit'].submit();</script>";
        return $sHtml;
	}

    //加密
    public function genSign($data){
        $str = "merchant=$data[merchant]&qrtype=$data[qrtype]&customno=$data[customno]&money=$data[money]&sendtime=$data[sendtime]&notifyurl=$data[notifyurl]&backurl=$data[backurl]&risklevel=$data[risklevel]".$this->signKey;
        $sign = md5($str);
        return $sign;
    }

    /**
     * 验签
     * @param $data
     * @return bool
     */
    public function checkNotifySign($data){
        $tmp = "merchant=$data[merchant]&qrtype=$data[qrtype]&customno=$data[customno]&sendtime=$data[sendtime]&orderno=$data[orderno]&money=$data[money]&paytime=$data[paytime]&state=$data[state]".$this->signKey;
        $getSign = md5($tmp);
        if( $data['sign'] == $getSign ){
            return true;
        } else {
            \Log::info("汇丰支付验签失败,返回签名:".$data['sign'].',生成签名:'.$getSign);
            return false;
        }
	}
}