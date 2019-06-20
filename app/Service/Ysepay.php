<?php

namespace App\Service;

class Ysepay{
	
	private $config = null;

    CONST QR_BANK_WECHAT = 1902000,
          QR_BANK_ALIPAY = 1903000;
	public function __construct(){
		$this->config = config('ysepay');
	}

    public function datetime2string($datetime) {
        return preg_replace('/\-*\:*\s*/', '', $datetime);
    }

    public function signCheck($sign, $data){
    	$publicKeyFile = $this->config['businessgatecerpath'];
    	$certificateCAcerContent = file_get_contents($publickeyFile);
		$certificateCApemContent = '-----BEGIN CERTIFICATE-----' . PHP_EOL . chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL) . '-----END CERTIFICATE-----' . PHP_EOL;
        $success = openssl_verify($data, base64_decode($sign), openssl_get_publickey($certificateCApemContent), OPENSSL_ALGO_SHA1);
		if($success){
			return true;
		}
		else{
			return false;
		}
    }

    public function signGen($input){
    	ksort($input);
        $signStr = urldecode(http_build_query($input));
    	$pkcs12 = file_get_contents($this->config['pfxpath']);
        if (openssl_pkcs12_read($pkcs12, $certs, $this->config['pfxpassword'])) {
            $privateKey = $certs['pkey'];
            $publicKey  = $certs['cert'];

            $signedMsg = "";
            if (openssl_sign($signStr, $signedMsg, $privateKey, OPENSSL_ALGO_SHA1)) {
                return base64_encode($signedMsg);
            }
        }
        return false;
    }

    public function createQrOrder($bankType,$orderInfo){
        $bizContent = [
            'out_trade_no'  =>  $orderInfo['out_trade_no'],
            'subject'   =>  $orderInfo['subject'],
            'total_amount'  =>  $orderInfo['amount'],
            'seller_id' =>  $this->config['partner_id'],
            'seller_name'   =>  $this->config['merchantname'],
            'timeout_express'   =>  '96h',
            // 'extend_params' =>  [
            //     'param1'   =>  'test'
            // ],
            'business_code' =>  '01000010',
            'bank_type' =>  $bankType,
        ];
        $param = [
            'method'    =>  'ysepay.online.qrcodepay',
            // 'method'    =>  'ysepay.online.directpay.createbyuser',
            'partner_id'    =>  $this->config['partner_id'],
            'timestamp' =>  date('Y-m-d H:i:s'),
            'charset'   =>  'UTF-8',
            'sign_type' =>  'RSA',
            'notify_url'    =>  $this->config['notify_url'],
            'version'   =>  '3.0',
            'return_url'    =>  $this->config['return_url'],
            'biz_content'   =>  json_encode($bizContent),
        ];
        $param['sign'] = $this->signGen($param);

        // var_dump($param);
        $multipart = [];
        foreach ($param as $key => $value) {
            $multipart[] = [
                'name'  =>  $key,
                'contents'  =>  $value,
            ];
        }
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://qrcode.ysepay.com/gateway.do', [
            'form_params'   =>  $param,
        ]);
        echo ((string)$response->getBody());
        return $param;
    }	
}