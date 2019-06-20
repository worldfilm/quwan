<?php
namespace App\Service;

/**
 * 聚创支付
 */
class JCpay{

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
	private $gateway = 'http://www.mdgctz.com/jczf/public/index.php/yimei/pay/alipay';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/jcpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = '7AImNuH0qvJp9UaQpnEUewjm9T52subu';
		$this->merchantId = 'jc00000638';
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
			\Log::error("[聚创支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = 'alipay';
                break;
			default:
				\Log::error("[聚创支付] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
            'notifyUrl'     => iconv("GBK","UTF-8", $this->notify_url),
            'outOrderNo'    => $orderInfo['out_trade_no'],
            'goodsClauses'  => "mac",
            'tradeAmount'   => number_format($orderInfo['amount'],2,'.',''),
            'code'          => $this->merchantId,
            'payCode'       => $service
		];

        $signParam['sign'] = $this->genSign($signParam);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signParam));
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output,true);
        \Log::info("聚创支付提交订单URL:".$this->gateway.",参数:".json_encode($signParam,320).",响应:".json_encode($result,320));
        return $result['url'];
	}

	//加密
    public function genSign($data){
        ksort($data);
        $str = '';
        foreach ($data as $key => $value) {
            if($value != ''){
                $str .= $key.'='.$value.'&';
            }
        }
        $str .= 'key='.$this->signKey;
        $sign = md5($str);
        return $sign;
    }

    /**
     * 验签
     * @param $param
     * @return bool
     */
	public function checkNotifySign($param){
        $jcSign = $param['sign'];
        unset($param['sign']);
        $getSign = $this->genSign($param);
        if( $jcSign == $getSign ){
            return true;
        } else {
            \Log::info("聚创验签失败,返回签名:".$jcSign.',生成签名:'.$getSign);
            return false;
        }
	}
}