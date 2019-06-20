<?php
namespace App\Service;
use QrCode;

/**
 * 金阳支付
 */
class JinYangpay{

	const QrCodeWX = 'WEIXIN';
	const QrCodeALI = 'ALIPAY';
	const H5PayWX = 'WEIXINWAP';
	const H5PayALI = 'ALIPAYWAP';
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
	private $gateway    = 'http://pay.095pay.com/zfapi/order/pay';
	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/jinyangpay";
		$this->signKey = '005b88b6dee9ab2cd40b889ffeaafdb2';
		$this->merchantId ='26481';
	}

	public function createOrder($payMethod, $method, $orderInfo,$isJump=1){
		if( $method ){
			$payBody = $this->createWapOrder($payMethod, $orderInfo,$method);
			$payHtmlUri = $payBody;
			return [
				'type'	=>	$method,
				'pay_html_uri'	=>	$payHtmlUri
			];
		}
		else{
			\Log::error("[JinYangPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$paytype = self::QrCodeWX;
				break;
			case 'alipay_qr':
				$paytype = self::QrCodeALI;
				break;
			case 'wechat_wap':
				$paytype = self::H5PayWX;
				break;
			case 'alipay_wap':
				$paytype = self::H5PayALI;
				break;
			default:
				\Log::error("[JinYangPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'p1_mchtid'			=> $this->merchantId,
			'p2_paytype'		=> $paytype,
			'p3_paymoney'		=> $orderInfo['amount'],
			'p4_orderno'		=> $orderInfo['out_trade_no'],
			'p5_callbackurl'	=> $this->notify_url,
			'p6_notifyurl'		=> '',
			'p7_version'		=> 'v2.8',
			'p8_signtype'		=> '1',
			'p9_attach'		=> '',
			'p10_appname'		=> '',
			'p11_isshow'		=> '0',
			'p12_orderip'		=> '',
		];

		\Log::info('[JinYangPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['sign'] = $sign;

		//请求
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->gateway);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($signParam));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response=curl_exec($ch);

        $result = json_decode($response,true);
dd($result) ;die;
        if($result['Code'] == '0' && isset($result['QrCode'])){
        	dd($result['QrCode']) ;
        }else{
        	return false;
        }
	}

	//加密
	public function genSign($param, $signKey){
		$signStr = urldecode( http_build_query($param) ) . $signKey;
		//dd($signStr);
		return md5($signStr);
	}

	public function genPayHtml($data, $orderInfo){
		$filename = time() . '_' . $orderInfo['out_trade_no'] . '.html';
		$uriPath = "/payhtmls/{$filename}";
		$path = public_path($uriPath);
		file_put_contents($path, $data);
		return $uriPath;
	}

	/**
	 * 验签
	 * @param  [type] $data     [description]
	 * @param  [type] $pay_type [description]
	 * @return [type]           [description]
	 */
	public function checkNotifySign($param){
		$originSign = $param['P_PostKey'];
		unset($param['P_PostKey']);
		$signStr = sprintf(
					"%s|%s|%s|%s|%s|%s|%s|%s|%s",
					$param['P_UserId'],
					$param['P_OrderId'],
					$param['P_CardId'],
					$param['P_CardPass'],
					$param['P_FaceValue'],
					$param['P_ChannelId'],
					$param['P_PayMoney'],
					$param['P_ErrCode'],
					$this->signKey
			);
		$callbackSign = md5($signStr);
		if( strcasecmp($callbackSign,$originSign) == 0 ){
			return true;
		}else{
			return false;
		}
	}
}