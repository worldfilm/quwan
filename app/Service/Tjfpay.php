<?php
namespace App\Service;

/**
 * 银付
 */
class Tjfpay{

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
	private $gateway = 'http://gate.iceuptrade.com/cooperate/gateway.cgi';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/tjfpay";
		$this->signKey = '51a985f7146063eaca1917a295386358';
		$this->merchantId = '2018012611010196';
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
			\Log::error("[天机付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = '1';
                break;
            case 'wechat_wap':
                $service = '2';
                break;
            case 'qq_wap':
                $service = '3';
                break;
            case 'jd_wap':
                $service = '5';
                break;
			default:
				\Log::error("[天机付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
            'service'   => "TRADE.H5PAY",
            'version'   => "1.0.0.0",
            'merId'     => $this->merchantId,
            'tradeNo'   => $orderInfo['out_trade_no'],
            'typeId'    => $service,
            'tradeDate' => date("Ymd"),
            'amount'    => number_format($orderInfo['amount'],2,'.',''),
            'notifyUrl' => iconv("GBK","UTF-8", $this->notify_url),
            'summary'   => "mac",
            'clientIp'  => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200)
		];
        $signParam['sign'] = $this->genSign($signParam);
        $sHtml = "<form name='h5PaySubmit' action='".$this->gateway."' method='post'>";
        foreach ($signParam as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['h5PaySubmit'].submit();</script>";
        return $sHtml;
	}

    /**
     * MD5加密
     * @param $params
     * @return string
     */
    public function genSign($params){
        $str = 'service='.$params['service'].'&version='.$params['version'].'&merId='.$params['merId'].'&typeId='.$params['typeId'].
            '&tradeNo='.$params['tradeNo'].'&tradeDate='.$params['tradeDate'].'&amount='.$params['amount'].'&notifyUrl='.$params['notifyUrl'].
            '&summary='.$params['summary'].'&clientIp='.$params['clientIp'].$this->signKey;
        $post_key = md5($str);
        return $post_key;
	}

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $tmp = 'service='.$param['service'].'&merId='.$param['merId'].'&tradeNo='.$param['tradeNo'].'&tradeDate='.$param['tradeDate'].
            '&opeNo='.$param['opeNo'].'&opeDate='.$param['opeDate'].'&amount='.$param['amount'].'&status='.$param['status'].
            '&extra='.$param['extra'].'&payTime='.$param['payTime'].$this->signKey;
        $getSign = md5($tmp);
        if( strcasecmp($getSign, $param['sign']) == 0 ){
            return true;
        } else {
            \Log::error("验签失败--->生成MD5:".$getSign.PHP_EOL."验签字符串:".$tmp);
            return false;
        }
	}
}