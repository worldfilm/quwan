<?php
namespace App\Service;

/**
 * 奇迹支付
 */
class QJPay{

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
    private $gateway = 'http://payf.whxyckj.com/Pay_API.aspx';

	public function __construct(){
        $this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/qjpay";
        $this->return_url = "http://".env("APP_DOMAIN")."/api/pay/rechargeH5";
        $this->signKey = '6a4d1ccc0685b14ffc9432ea00bfaa74';
        $this->merchantId = '10371';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payHtmlUri = $this->createWapOrder($payMethod, $orderInfo,$method);
			if(!$payHtmlUri){
				return false;
			}

			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[QJ支付]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){

        //选择支付方式
        switch ($payMethod.'_'.$method) {
            case 'alipay_wap':
                $service = "1";
                break;
            case 'wechat_wap':
                $service = "20";
                break;
            default:
                \Log::error("[QJ支付] type error {$payMethod}");
                return false;
                break;
        }

        //签名准备
        $param = [
            'P_UserId'     => $this->merchantId,
            'P_OrderId'    => $orderInfo['out_trade_no'],
            'P_FaceValue'  => number_format($orderInfo['amount'],2,'.',''),
            'P_ChannelId'  => $service,
            'P_CardId'	   => "",
            'P_CardPass'   => "",
            'P_Price'      => number_format($orderInfo['amount'],2,'.',''),
            'P_Quantity'   => 1,
            'P_Result_URL' => iconv("GBK","UTF-8", $this->notify_url),
            'P_Notify_URL' => iconv("GBK","UTF-8", $this->notify_url),
        ];

        //请求参数准备
        $param['P_PostKey'] = $this->genSign($param);

        return $payUrl = $this->gateway . '?' . http_build_query($param);
	}

	//加密
    public function genSign($param){
        $signStr = $param['P_UserId']."|".$param['P_OrderId']."|".$param['P_CardId']."|".$param['P_CardPass']."|".$param['P_FaceValue']."|".$param['P_ChannelId']."|".$this->signKey;
        return md5($signStr);
    }

    /**
     * 验签
     * @param $data
     * @return bool
     */
    public function checkNotifySign($data){
        $signKey = "499286be1b4acb49441714729a14cc94";
        $tmp = $data['P_UserId']."|".$data["P_OrderId"]."|".$data["P_FaceValue"]."|".$data["P_Notic"]."|". $data["P_Return"]."|".$data["P_ErrCode"]."|".$data["P_SuccTime"]."|".$signKey;
        $getSign = md5($tmp);
        if( $data['P_PostKey'] == $getSign ){
            return true;
        } else {
            \Log::info("奇迹支付验签失败,返回签名:".$data['P_PostKey'].',生成签名:'.$getSign);
            return false;
        }
    }
}