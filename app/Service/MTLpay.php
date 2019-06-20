<?php
/**
 * Created by PhpStorm.
 * User: nicho
 * Date: 2018/8/8
 * Time: 11:49
 */


namespace App\Service;

class MTLpay
{
    //商户id
    const MERCHANTID = 'kezxc656';

    //密钥
    const SECRETKEY = '0cf890f995364aa118a671f4ff9a5004';

    //微信h5
    const WECHAT_H5 = 'WX';

    //支付宝h5
    const  ALIPAY_H5 = 'ALI';

    //微信扫码
    const WECHAT_QR = 'WX_SCAN';

    //统一h5下单地址
    const MAKE_ORDER_URL = 'http://weixin.9xen12.cn/platform/pay/unifiedorder/video';

    //统一扫码地址
    const MAKE_QR_URL = 'http://weixin.19uv18.cn/platform/pay/unifiedorder/video';

    //支付码扫码
    const ALIPAY_QR = 'ALI_SCAN';

    //异步通知地址
    private $notifyUrl = null;

    //同步通知地址
    private $redirectUrl = null;

    public function __construct()
    {
        $this->notifyUrl = "http://".env("APP_DOMAIN")."/api/pay/notify/mtlpay";
        $this->redirectUrl = "http://".env("APP_DOMAIN")."/api/pay/h5";
    }


    public function createOrder($type, $method ,$orderInfo)
    {
        if( $method  ){
            $requestData = [];
            $requestData['mch_id'] = self::MERCHANTID;
            $requestData['body'] = $orderInfo['title'];
            $amount = $orderInfo['amount'] * 100;
            $requestData['total_fee'] = (string)$amount;
            $requestData['out_trade_no'] = $orderInfo['out_trade_no'];
            $requestData['trade_type'] = $this->getTradeType($type,$method);
            if(!$requestData['trade_type']){
                return false;
            }
            $requestData['spbill_create_ip'] = $orderInfo['payerIp'];
            $requestData['notify_url'] = $this->notifyUrl;
            $requestData['redirect_url'] = $this->redirectUrl;
            $sign = $this->makeSign($requestData,self::SECRETKEY);
            $requestData['sign'] = $sign;
            $payUrl = $this->makeHtml($requestData,$method);
            return [
                'type'	=>	$method,
                'pay_html_uri'	=>	$payUrl,
            ];
        }
        else{
            \Log::error("[SRpay]\ttype error\t{$type}\t{$method}");
        }
    }

    /**
     * @param array $param
     */
    protected function makeHtml(array $param,string $method)
    {
        $data["sign"] = $param['sign'];//签名
        $data["mch_id"] = $param['mch_id'];
        $data["body"] = $param['body'];
        $data["total_fee"] = $param['total_fee'];
        $data["spbill_create_ip"] = $param['spbill_create_ip'];
        $data["notify_url"] = $param['notify_url'];
        $data["redirect_url"] = $param['redirect_url'];
        $data["trade_type"] = $param['trade_type'];
        $data["out_trade_no"] = $param['out_trade_no'];
        $httpData = http_build_query($data);
        //扫码或wap
        switch ($method){
            case 'wap':
                return self::MAKE_ORDER_URL.'?'.$httpData;
                break;
            case 'qr':
                return self::MAKE_QR_URL.'?'.$httpData;
                break;
        }
    }

    /**
     * 生成签名
     * @param array $param
     */
    public function makeSign(array $param,string $key)
    {
        $param = array_filter($param);
        ksort($param);
        $signData = $this->httpBuidStr($param);
        return md5($signData.'&key='.$key);
    }

    /**
     * 转成httpstr
     * @param array $param
     */
    protected function httpBuidStr(array $param)
    {
        $str = '';
        foreach ($param as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str = rtrim($str,'&');
        return $str;
    }

    /**
     * 得到支付类型
     * @param string $type
     */
    private function getTradeType($type, $method)
    {
        switch ($type.'_'.$method){
            case 'alipay_wap':
                $payType = self::ALIPAY_H5;
                break;
            case 'alipay_qr':
                $payType = self::ALIPAY_QR;
                break;
            case 'wechat_wap':
                $payType = self::WECHAT_H5;
                break;
            case 'wechat_qr':
                $payType = self::WECHAT_QR;
                break;
            default:
                \Log::error("[Goldenpay] type error {$type}");
                return false;
                break;
        }
        return $payType;
    }



}

