<?php
namespace App\Service;

/**
 * KK支付
 */
class KKpay{

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
	private $gateway = 'http://47.101.45.129:37903/payment/PayUnApply.do';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/kkpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = 'FoltFQk9cJ8V';
		$this->merchantId = 'TM10100318';
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
			\Log::error("[KK支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = '00028';
                break;
			default:
				\Log::error("[KK支付] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $signParam = [
            'versionId'     => "1.1",
            'orderAmount'   => $orderInfo['amount'] * 100,
            'orderDate'     => date('YmdHis'),
            'currency'      => "RMB",
            'transType'     => "0008",
            'asynNotifyUrl' => iconv("GBK","UTF-8", $this->notify_url),
            'synNotifyUrl'  => $this->return_url,
            'signType'      => "MD5",
            'merId'         => $this->merchantId,
            'prdOrdNo'      => $orderInfo['out_trade_no'],
            'payMode'       => $service,
            'receivableType'=> "D00",
            'prdName'       => "mac",
            'merParam'      => "book"
        ];

        $signParam['signData'] = $this->genSign($signParam);

//        $sHtml = "<form name='h5PaySubmit' action='".$this->gateway."' method='post'>";
//        foreach ($signParam as $key => $val) {
//            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
//        }
//        $sHtml.= "</form>";
//        $sHtml.= "<script>document.forms['h5PaySubmit'].submit();</script>";
//        return $sHtml;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signParam));
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output,true);
        $payUrl = substr(strstr($result['htmlText'],"href="),6,strpos(strstr($result['htmlText'],"href="), ';}')-7);
        \Log::error("R项目KK支付提交URL:".$this->gateway.",提交参数:".json_encode($signParam,320).",接收响应:".$output);
        return $payUrl;
//        $sHtml = "<html><script>window.location.href='".$payUrl."';</script><body></body></html>";
//        return $sHtml;
	}

    //加密
    public function genSign($data){
        ksort($data);
        $string='';
        foreach ($data as $key => $value){
            if($value != ''){
                $string .= $key.'='.$value.'&';
            }
        }
        $string .= 'key='. $this->signKey;
        $sign=strtoupper(md5($string));
        return $sign;
    }

    /**
     * 验签
     * @param $param
     * @return bool
     */
	public function checkNotifySign($param){
        $kkSign = $param['signData'];
        unset($param['signData']);
        $signStr = $this->genSign($param);
        if( $kkSign == $signStr ){
            return true;
        } else {
            \Log::info("KK支付验签失败,返回签名:".$kkSign.',生成签名:'.$signStr);
            return false;
        }
	}
}