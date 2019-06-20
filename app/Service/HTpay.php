<?php
namespace App\Service;
use QrCode;

/**
 * 汇天支付
 */
class HTpay{

	const QrCode = '21',
		  H5Pay = '33';
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
	private $gateway    = 'https://gateway.999pays.com/pay/KDBank.aspx';//'http://pay.jiatonglian.com/gateway/payment';

	public function __construct(){
		$this->notify_url = "http://".env("APP_DOMAIN")."/api/pay/notify/htpay";
		$this->signKey = '75ce7bfaefce4d548356b7e5ac60d462'; //test:'797840846e48be64173e9d6024ea5761';
		$this->merchantId ='1003878'; //test:'test001';
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
			\Log::error("[HTPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo,$method){
		//选择支付方式
		switch ($payMethod.'_'.$method) {
			case 'wechat_qr':
				$service = self::QrCode;
				$typeId  = 2;
				break;
			case 'alipay_qr':
				$service = self::QrCode;
				$typeId  = 1;
				break;
			case 'wechat_wap':
				$service = self::H5Pay;
				$typeId  = 2;
				break;
			case 'alipay_wap':
				$service = self::H5Pay;
				$typeId  = 1;
				break;
			default:
				\Log::error("[HTPay] type error {$payMethod}");
				return false;
				break;
		}

		//签名准备
		$signParam = [
			'P_UserId' => $this->merchantId,
			'P_OrderId' => $orderInfo['out_trade_no'],
			'P_CardId' => 0,
			'P_CardPass' => 0,
			'P_FaceValue' => $orderInfo['amount'],
			'P_ChannelId' => $service,
			'P_Subject' => '官方支付',
			'P_Price' => $orderInfo['amount'],
			'P_Quantity' => 1,
			'P_Result_URL' => iconv("GBK","UTF-8", $this->notify_url),
			'P_IsSmart' => 1,
		];

		\Log::info('[HTPay] sign array'.json_encode($signParam));

		//获取加密信息
		$sign = $this->genSign($signParam,$this->signKey);
		//请求参数准备
		$signParam['P_PostKey'] = $sign;

		return $payUrl = $this->gateway . '?' . http_build_query($signParam);
	}

	//加密
	public function genSign($param, $signKey){
		$signStr = sprintf(
					"%s|%s|%s|%s|%s|%s|%s",
					$param['P_UserId'],
					$param['P_OrderId'],
					$param['P_CardId'],
					$param['P_CardPass'],
					$param['P_FaceValue'],
					$param['P_ChannelId'],
					$signKey
			);

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