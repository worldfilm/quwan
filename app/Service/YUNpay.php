<?php
namespace App\Service;

/**
 * 云支付
 */
class YUNpay{

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
	private $gateway = 'http://mms.weixin881.com:8000/ltPayBusiness/order/prepareOrder';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/yunpay";
		$this->return_url = "http://".env("APP_DOMAIN")."/api/pay/h5";
		$this->signKey = '5b4680e3d73d64ff9e78ae57132036ba';
		$this->merchantId = '10827';
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
			\Log::error("[云支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
            case 'wechat_wap':
                $service = 'wechat_app';
                break;
            case 'alipay_wap':
                $service = 'alipay_wap';
                break;
			default:
				\Log::error("[云支付] type error {$payMethod}");
				return false;
				break;
		}

        //签名准备
        $signParam = [
            'version'    => "1.0",
            'merId'      => $this->merchantId,
            'orderId'    => $orderInfo['out_trade_no'],
            'totalMoney' => $orderInfo['amount'] * 100,
            'tradeType'  => $service,
            'describe'   => "mac",
            'notify'     => iconv("GBK","UTF-8", $this->notify_url),
            'redirectUrl'=> iconv("GBK","UTF-8", $this->return_url),
            'remark'     => "res",
            'fromtype'   => "wap2",
            'ip'         => rand(100,255).'.'.rand(50,150).'.'.rand(1,99).'.'.rand(1,200)
        ];
        $signParam['sign'] = $this->genSign($signParam);

        $data = json_encode($signParam);
        $rs = $this->curl_post_https($this->gateway, $data);
        $response = json_decode($rs,true);

        \Log::error("云支付提交参数:".json_encode($signParam,320).",返回:".$rs);
        $result = $service == 'wechat_app' ? $response['object']['wxPayWay'] : $response['object']['aliPayWay'];
        return $result;
	}

    /**
     * POST方法
     * @param $url
     * @param $data
     * @return mixed
     */
    public function curl_post_https($url, $data){ // 模拟提交数据函数
        $headers=array('Content-Type: application/json');
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }

    /**
     * 加密
     * @param $data
     * @return string
     */
    public function genSign($data){
        $str = 'merId='.$data['merId'].'&orderId='.$data['orderId'].'&totalMoney='.$data['totalMoney'].'&tradeType='.$data['tradeType'].'&'.$this->signKey;
        $sign = strtoupper(md5($str));
        return $sign;
    }

    /**
     * 验签
     * @param $param
     * @return bool
     */
    public function checkNotifySign($param){
        $notifyKey = "5b4680e3d73d64ff9e78ae57132036ba";
        $my_str = "code" . $param['code'] . "merId" . $param['merId'] . "money" . $param['money'] . "orderId" . $param['orderId'] . "payWay" . $param['payWay'] .
            "remark" . $param['remark'] . "time" . $param['time'] . "tradeId" . $param['tradeId'] . $notifyKey;
        $my_sign = strtoupper(md5($my_str));
        if( $my_sign == $param['sign'] ){
            return true;
        } else {
            \Log::info("云支付验签失败,返回签名:".$param['sign'].',生成签名:'.$my_sign);
            return false;
        }
    }
}