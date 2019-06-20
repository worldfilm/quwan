<?php

namespace App\Service;

use Exception;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Omnipay\Omnipay;

/**
 *   支付宝服务
 *   @auther morgan
 */
class AlipayService
{
    /**
     * 支付宝公钥
     */
    const AlipayPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgDBlkv0/3yC8w7OJW7o99jGQVvARZ/ZP+wt7OFRDdsV9B7fDC2T/kV/T86ZCCWAgIz04aPfGNMVtBrJU0tokTrVQ/s8d7wbM+3nI33K8fo7cxyUsMFZg3VxhzxNYNHAKVOpv+Um0LSZFf+fC1FV7pD0To6YVdMAUdGkig7J3znVN6cKuPTSvlp7+IDl3RaQPipX2WyFuvVRV4Qp2N/BRF6HyUF03fZ69kSAno5Z+JA5oXrdzubk6elU/ztexzXDO7VIN4mZLxT7HWVh1Da15gdl+F/AlDt2+KcmyMmamzNZ2iOtokyngVwe4L2ITkghTk1ZOKVlRMFAK0ZA0sFdsNwIDAQAB";

    /**
     * 商户私钥
     */
    const AppPrivateKey = "MIIEowIBAAKCAQEAzZ+1keF1mapG+y2yLwJuNNc2pT6fcSptPzerjnP4gNg6melAQz+2hzo0s/dyMl7X4BrPjsIh3tzyxSqeuveheYdoyoQ+j0TSIVfFlZ3bZMF+NyEjPlAz0d4zsgzQodINBbNYNRDNVMC13iTN5orP74j+qN38mwt93/E7c/N9t0ZJeP/uazE33QQ88yPb7+Vh2z3DZAr7uHGfQKY5Xubu9WaKo7ZBBmAM0D2bO08/VKqTngRJe1pf11pArqen4OMqHtuwjV308jLwixs/XE/Q81ihrbG5WK7KBjgEhGlJgWnY6pgt1bCpRT420Gi26Fq23Si1OE4Alozb79wosUWzgwIDAQABAoIBAQCx4Cfcw3HM9W0j2hra6bpWQZpHBk49QhtxZYIl4BKnuuWuwQVTZ2lJv8NZr4P9KOiMOAU3FJ7iQLZOc5kOWis4izfOgOnxW/J+34PP8teYaH37yNHJFO5O7W5A5y8P3TpgpQ14EknduDtbqPDpL0nDh/nsMORVZ3KDaBYrLwagpELJ8NSsFTkoMEh89K7aVQ7pBesynCX7PYgw6rqV/yR4sTXzOFSA+UIXq2NcbD63ZnpY6bUYEkJLSrxAKDsKfe+mutnMoImMk0nLAmOei1CbFL7VMiHQN+o4XhHnWx85L1wximno4FdLaf4cJrJ72kpQtbr8mG3gkdsf101sKRYZAoGBAOw/guIqUoH2ZyX1/A6PHlHbA6sPank8l58DdYjDaRoJ5gULWSQvbTqvX+PgohCXjaA1zW8Ajt9MEk09iKW+qdY0DoIFGvu/bnGeq0kEGPCbI/noR+PUJk/VVmCoijb5qOPsHAC0cjKbxlAQssr8Ba9f1kHTBjus0PQPOAGD6YJ3AoGBAN7QvQ2RXC7aOuHxk6upDcnXqh7LYT1bp0RMw8JPQJJZEsFHnYvYEJ+F4HGH+B86uvJMjBkSGIf+AxF9I6wITy6ycM8vLo7OlPIGCQFDc8EZhO28PhQOdQ73zI5FyuBQ5W6RD8GEvQwQhVr3A2/n2WbcyPsTze7boz5EDbmyFC5VAoGAZicoPwcx2gRjoaR1sw3rfFWJhoQJ9BZhMV7biaGFZr70+SVpDB59yqxeeDh/m1EM6kOZavAA10kbeM6ssY55/adQxqPSgRzLctG7Gr1s000iB0OLIlZvooGK8gyuhwr5HmTPzvY3ku6Ml75AsUi8ZJK6IIhQ36jgSUfzXaReag0CgYBLJ0rs/Z+DKhZ8SThOySmecACwfFbjFDv53I6WxpZ4BlU+HgK0vX7133kRshPtrKiLu2fxuNnPXMz6JCRZDUBv9r34E+j3QBSbAZAd/ftYIVxlt32U1D+Fee1j+4RvXrnULleLJVOCczwXr4NYRnJcsJGVpltqemi4QsoCXV9TNQKBgHh4kEK53K6V1wUnULuFqkF711QRPhZmwb5moK6qOJFyetd8ucjmvIHbKN5dx3f2US4ypkSc0S2I/sehF0MPbU1ZZ+b6RNTutfSHghNbo6CbYd2T+fwp+Uf1XZfT2vJbo70+YAf5oUzPWlCzybIoqZir27+u7m4os8iZfQFZ76a5";

    protected $gateway;

    public function __construct(){
        $this->gateway =Omnipay::create('Alipay_AopApp');
        $this->gateway->setSignType('RSA2'); //RSA/RSA2
        $this->gateway->setAppId('2018061960374686');
        $this->gateway->setPrivateKey( self::AppPrivateKey );
        $this->gateway->setAlipayPublicKey( self::AlipayPublicKey );
        $this->gateway->setNotifyUrl('http://www.vinnosmart.com/api/alipay/notify');
    }

    /**
     * [appOrder app支付返回手机端SDK数据]
     * @param  [type] $request [description]
     * @param  [type] $option  [description]
     * @return [type]          [description]
     */
    public  function appOrder( $request, $option )
    {
        // 创建支付单。
        $response =  $this->gateway->purchase()->setBizContent([
            'subject'      => $option['goods_name'],
            'out_trade_no' => $option['out_trade_no'],
            'total_amount' => $option['price'],
            'product_code' => 'QUICK_MSECURITY_PAY',
        ])->send();

        // 返回签名后的支付参数给支付宝移动端的SDK。

        return $response->getOrderString();
    }


    /**
     * [alipayNotify 手机端异步通知]
     * @return [type] [description]
     */
    public function alipayNotify( $data )
    {
        $request = $this->gateway->completePurchase();
        $request->setParams($data);//Optional

        /**
         * @var AopCompletePurchaseResponse $response
         */
        try {
            $response = $request->send();
            if($response->isPaid()){
                /**
                 * Payment is successful
                 */
                return true; //The response should be 'success' only
            }else{
                /**
                 * Payment is not successful
                 */
                return false;
            }
        } catch (Exception $e) {
            /**
             * Payment is not successful
             */
            return false;
        }
    }
}