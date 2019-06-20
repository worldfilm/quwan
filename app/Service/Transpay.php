<?php
namespace App\Service;
// use Spatie\ArrayToXml\ArrayToXml;
// use QrCode;
// 转账支付
class Transpay{

    function __construct(){
        $this->config = config('transpay');
    }

    public function createOrder($type = 'wechat', $method, $orderInfo){
        if( $type == 'wechat' ){
            $wxInfo = $this->getWxInfo();
            return [
                'type'  =>  'trans',    //转账
                'account'   =>  $wxInfo['wx_account'],  //转账账号
                'message'   =>  $orderInfo['out_trade_no'],//附言码，使用out_trade_no
            ];
        }
        elseif( $type == 'alipay' ){
            $alipayInfo = $this->getWxInfo();
            return [
                'type'  =>  'trans',    //转账
                'account'   =>  $alipayInfo['alipay_account'],
                'message'   =>  $orderInfo['out_trade_no'],//附言码，使用out_trade_no
            ];
        }
        else{
            \Log::error("[WxTranspay]\ttype error\t{$type}\t{$method}");
        }
    }

    private function getWxInfo(){
        //获取可用微信账号信息
        $client = new \GuzzleHttp\Client();
        $param = [
            'time'  =>  time(),
        ];
        $sign = md5($this->config['key'] . $param['time']);
        $param['sign'] = $sign;
        $url = $this->config['gateway'] . '/app/w/newlists' . '?' . http_build_query($param);
        $response = $client->request('GET', $url);
        $bodyData = json_decode( (string)$response->getBody(),true );
        $accountList = $bodyData['data']['number'];
        $account = $accountList[array_rand($accountList)];
        return [
            'wx_account'    =>  $account,
        ];
    }

    private function getAliPayInfo(){
        //获取可用支付宝账户信息(假设支持支付宝)
        return [
            'alipay_account'    =>  ''
        ];
    }

    public function genSign($data){

    }

    public function checkNotifySign($data){
        $originSign = $data['sign'];
        $sign = md5($data['time'] . $data['message'] . $data['amount'] . $this->config['check_key']);

        if( $originSign == $sign ){
            return true;
        }
        else{
            \Log::error("[Transpay]\t" . json_encode($data) . "\t" . $sign);
            return false;
        }
    }
}