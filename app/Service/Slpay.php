<?php
namespace App\Service;

/**
 * 速龙支付
 */
class Slpay{

    /**
     * 速龙公钥
     */
    const rsaSignKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCd+YGddjNlgXo8Nw2ckswSj23ZEmrHbb4SuIcklOkQ6ah+IBbAn5tIBbkoFiPyyGlt0r8UAqUwHM6+PVjLMgDEs8vNamJcMyrmJydC3S0xV7Z9usN8IwGTPw8ginDWxNPSCtmBrqXeNAmUEIbbqM8KzLPVfzB1pH4eAZ2wATTcPwIDAQAB
-----END PUBLIC KEY-----';

    /**
     * 商户私钥
     */
    const merchantPrivateKey ='-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAOnL6fLUjWT56nYp
uPDRtKIBajIjhbWgOmVfDzBSTkz7oczSrhT4IH0FbKUTNk9U5g9H4e0p25t5EhPM
/JOCNNYVmtW26W6O3HCN0g1Fy+XFkhj6QUoQo3PX9S8lpIt+znGjRT/EU+3YQr3S
CGGKlpBgcQzBy3+a6vWh5QFUtlTnAgMBAAECgYBqGCr2ExaG+BI5xP0z6zX5PoMe
dZg7r5ZQYi7WUsNNk/L8q65rvZ2gnlGLJ4jBv1kXHhucMB6EExDtA1yq469tiRiD
o04pFbhDnYSafqoUfUKgxHy2hFq8eSQDSAlE1HwAdNgOb3jSxKEIIEX2yIiF6Ehj
tsFPdpYP8DpFmcQEYQJBAPgll4HfLSUroHoZxHHLfh34YnLAU4eCCE6+CcBMfOPt
lDwev5V1Wt2Kb8KXyT6IIodo0hL57pplTNVBrXrws1UCQQDxMg9zT8UUs5cckSKv
Hsfud/PTT0lehxcB01v4Qm0shHn075aaKkUvgWoKxgc29Kc0QalRxnXWFs1G/Ugt
kJ9LAkAqDhzuSseY7BrndqR/cLBwHd95eTTu20/TIIwAhjYIXwRnaAKqLth2gXbN
cPIPYf0QG+i2hJs2mYJ7BgWDt3V9AkEAn9bEBG/dtF1bjBPmf1UPu8oEbInDnoA5
z/zomvoybWkRhS9th5bxqIzD4IXhbBrv36KP+eBiYNFVknDxbzjDRwJAMJUTIL0k
qKvzSKe4Mk2rs5Z8H7zpxVaxRa3M0bNZpuPyfCMeWhufl79fkIM2a4qY/Hg65fy1
ZGuK3YSnBV1IGA==
-----END PRIVATE KEY-----';


    /**
     * 商户号
     * @var string
     */
    const merchantId = '103103103004';

    /**
     * 异步通知地址
     * @var string
     */
    private $notify_url = '';

    /**
     * 请求网关
     * @var string
     */
    private $gateway = 'https://pay.islpay.hk/gateway?input_charset=UTF-8';

	public function __construct(){
        $this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/slpay";
	}

	public function createOrder($type, $method, $orderInfo, $isJump=1){

		if( $method ){
			$payBody = $this->createWapOrder($type.'_'.$method, $orderInfo);
			if(!$payBody){
				return false;
			}

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payBody
			];
		}
		else{
			\Log::error("[Slpay]\ttype error\t{$type}\t{$method}");
		}
	}

	public function createWapOrder($type, $orderInfo){

		//选择支付方式
		switch ($type) {
            case 'alipay_wap':
                $service = 'h5_ali';
                break;
            case 'wechat_wap':
                $service = 'h5_wx';
                break;
			default:
				\Log::error("[ZtbaoPay] type error {$type}");
				return false;
				break;
		}

        //签名准备
        $requestData = [
            'merchant_code'     => self::merchantId,
            'service_type'      => $service,
            'notify_url'        => iconv("GBK","UTF-8", $this->notify_url),
            'interface_version' => "V3.0",
            'input_charset'     => "UTF-8",
            'client_ip'         => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200),
            'order_no'          => $orderInfo['out_trade_no'],
            'order_time'        => date('Y-m-d H:i:s'),
            'order_amount'      => $orderInfo['amount'],
            'product_name'      => "mac"
        ];

        //获取加密信息
        $requestData['sign'] = $this->genSign($requestData);
        $requestData['sign_type'] = "RSA-S";

        $sHtml = "<form name='h5PaySubmit' action='".$this->gateway."' method='post'>";
        foreach ($requestData as $key => $val) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml.= "</form>";
        $sHtml.= "<script>document.forms['h5PaySubmit'].submit();</script>";
        return $sHtml;
	}

    /**
     * 加密
     * @param $param
     * @return string
     */
    public function genSign($param){
        ksort($param);
        $signStr = '';
        foreach ($param as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }
        $signStr = substr($signStr,0,strlen($signStr)-1);
        $merchant_private_key = openssl_get_privatekey(self::merchantPrivateKey);
        openssl_sign($signStr,$sign_info,$merchant_private_key,OPENSSL_ALGO_MD5);
        $sign = base64_encode($sign_info);
        \Log::info("速龙参与签名:".json_encode($param,320).',签名字符串:'.$signStr.',生成签名:'.$sign);
        return $sign;
    }

    /**
     * 验签
     * @param $data
     * @return bool
     */
    public function checkNotifySign($data){
        ksort($data);
        $sign = base64_decode($data["sign"]);
        unset($data['sign']);
        unset($data['sign_type']);

        $signStr = '';
        foreach ($data as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }
        $signStr = substr($signStr,0,strlen($signStr)-1);

        $dinpay_public_key = openssl_get_publickey(self::rsaSignKey);

        $flag = openssl_verify($signStr,$sign,$dinpay_public_key,OPENSSL_ALGO_MD5);

        if( $flag ){
            return true;
        }else{
            \Log::error("速龙支付验签失败,订单号:".$data['order_no']);
            return false;
        }
    }
}