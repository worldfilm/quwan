<?php
namespace App\Service;

/**
 * 优米付
 */
class Ymfpay{

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
	private $gateway = 'http://cashier.youmifu.com/cgi-bin/netpayment/pay_gate.cgi';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/ymfpay";
		$this->signKey = 'f27f2844c4d026b1ef7d46f02100190c';
		$this->merchantId = '856086360013357';
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
			\Log::error("[优米付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = '10';
                break;
            case 'wechat_wap':
                $service = '13';
                break;
            case 'qq_wap':
                $service = '14';
                break;
			case 'wechat_qr':
				$service = '5';
				break;
            case 'qq_qr':
                $service = '6';
                break;
			default:
				\Log::error("[优米付] type error {$payMethod}");
				return false;
				break;
		}

		if($service == '20'){
            //签名准备
            $signParam = [
                'apiName'       => "WAP_PAY_B2C",
                'apiVersion'    => '1.0.0.0',
                'platformID'    => $this->merchantId,
                'merchNo'       => $this->merchantId,
                'merchUrl'      => iconv("GBK","UTF-8", $this->notify_url),
                'choosePayType' => $service,
                'orderNo'       => $orderInfo['out_trade_no'],
                'tradeDate'     => date('Ymd'),
                'amt'           => $orderInfo['amount'],
                'merchParam'    => 'victory',
                'tradeSummary'  => 'macBook'
            ];
            $signParam['signMsg'] = $this->genSign($signParam);
        } else {
            //签名准备
            $signParam = [
                'apiName'       => "WEB_PAY_B2C",
                'apiVersion'    => '1.0.0.1',
                'platformID'    => $this->merchantId,
                'merchNo'       => $this->merchantId,
                'merchUrl'      => iconv("GBK","UTF-8", $this->notify_url),
                'choosePayType' => $service,
                'orderNo'       => $orderInfo['out_trade_no'],
                'tradeDate'     => date('Ymd'),
                'amt'           => $orderInfo['amount'],
                'merchParam'    => 'victory',
                'tradeSummary'  => 'macBook',
                'customerIP'    => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200)
            ];
            $signParam['signMsg'] = $this->genTenPaySign($signParam);
        }

//        \Log::error("优米付提交参数:".json_encode($signParam,320));

        $sHtml = "<form id='mobaopaysubmit' name='mobaopaysubmit' action='".$this->gateway."' method='post'>";
        while (list ($key, $val) = each ($signParam)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['mobaopaysubmit'].submit();</script>";
        return $sHtml;
	}

    /**
     * MD5加密
     * @param $data
     * @return string
     */
    public function genSign($data){
        $str = sprintf(
            "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s&merchUrl=%s&merchParam=%s&tradeSummary=%s",
            $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['merchUrl'], $data['merchParam'], $data['tradeSummary']
        );
        $post_key = strtoupper(md5($str.$this->signKey));
        return $post_key;
	}

    /**
     * MD5加密
     * @param $data
     * @return string
     */
    public function genTenPaySign($data){
        $str = sprintf(
            "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s&merchUrl=%s&merchParam=%s&tradeSummary=%s&customerIP=%s",
            $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['merchUrl'], $data['merchParam'], $data['tradeSummary'],$data['customerIP']
        );
        $post_key = strtoupper(md5($str.$this->signKey));
        return $post_key;
    }

    /**
     * 验签
     * @param $data
     * @return bool
     */
    public function checkNotifySign($data){
        $tmp = sprintf(
            "apiName=%s&notifyTime=%s&tradeAmt=%s&merchNo=%s&merchParam=%s&orderNo=%s&tradeDate=%s&accNo=%s&accDate=%s&orderStatus=%s",
            $data['apiName'], $data['notifyTime'], $data['tradeAmt'], $data['merchNo'], $data['merchParam'], $data['orderNo'], $data['tradeDate'], $data['accNo'], $data['accDate'], $data['orderStatus']
        );
        $getSign = strtoupper(md5($tmp.$this->signKey));
        if( strcasecmp($getSign, $data['signMsg']) == 0 ){
            return true;
        } else {
            \Log::error("验签失败--->生成MD5:".$getSign.PHP_EOL."验签字符串:".$tmp);
            return false;
        }
	}
}