<?php
namespace App\Service;
use QrCode;

/**
 * 如一付
 */
class Rypay{

	/**
	 * 微信公众号(微信跳转 wap)
	 * @var [type]
	 */
	private $wechatWap = [
			'appId' => 33,
	];

	/**
	 * 微信扫码支付
	 * @var [type]
	 */
	private $wechatQr  = [
			'appId' => 21,
	];

	/**
	 * 支付宝扫码支付
	 * @var [type]
	 */
	private $alipayQr = [
			'appId' => 2,
	];

	/**
	 * 支付宝手机（支付宝跳转 wap）
	 * @var [type]
	 */
	private $alipayWap = [
			'appId' => 36,
	];

	/**
	 * QQ 手机支付
	 * @var [type]
	 */
	private $qqWap = [
			'appId' => 92,
	];

	/**
	 * QQ 扫码支付
	 * @var [type]
	 */
	private $qqQr = [
			'appId' => 89,
	];

	/**
	 * JD wap支付
	 * @var [type]
	 */
	private $jdWap = [
			'appId' => 91,
	];


	/**
	 * 支付宝公众号WAP
	 * @var [type]
	 */
	private $alipayPublicWap = [
			'appId' => 93,
	];

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
	 * 同步通知地址
	 * @var string
	 */
	private $success_url = '';

	/**
	 * 请求网关
	 * @var string
	 */
	private $gateway = 'https://gateway.ruyipay.com/Pay/KDBank.aspx';

	public function __construct(){
		$this->notify_url   = "http://".env("APP_DOMAIN")."/api/pay/notify/rypay";
		$this->success_url  = "http://".env("APP_DOMAIN")."/api/pay/success";
		$this->merchantId   = '1004178';
		$this->signKey      = 'ce38219c405a42388e29723768df410f';
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
			\Log::error("[startPay]\ttype error\t{$payMethod}\t{$method}");
		}
	}

	public function createWapOrder($payMethod, $orderInfo, $method){

        //选择支付方式
        switch ($payMethod.'_'.$method) {
            case 'wechat_qr':
                $appid    = $this->wechatQr['appId'];
                break;
            case 'alipay_qr':
                $appid    = $this->alipayQr['appId'];
                break;
            case 'qq_qr':
                $appid    = $this->qqQr['appId'];
                break;
            case 'wechat_wap':
                $appid    = $this->wechatWap['appId'];
                break;
            case 'alipay_wap':
                $appid    = $this->alipayWap['appId'];
                break;
            case 'qq_wap':
                $appid    = $this->qqWap['appId'];
                break;
            case 'jd_wap':
                $appid    = $this->jdWap['appId'];
                break;
            default:
                \Log::error("[RyPay] type error {$payMethod}");
                return false;
                break;
        }

		//签名准备
		$param = [
            'P_UserId'      => $this->merchantId,
            'P_OrderId'     => $orderInfo['out_trade_no'],
            'P_CardId'      => "",
            'P_CardPass'    => "",
            'P_FaceValue'   => intval($orderInfo['amount']),
            'P_ChannelId'   => $appid,
            'P_Description' => "",
            'P_Subject'     => "iphone",
            'P_Price'       => intval($orderInfo['amount']),
            'P_Quantity'    => 1,
            'P_Result_URL'  => iconv("GBK","UTF-8", $this->notify_url)
		];

		\Log::info('[RyPay] param array'.json_encode($param));

		//获取加密信息
		$sign = $this->genSign($param,$this->signKey);
		$param['P_Result_URL'] = $this->notify_url;
		$param['P_Price']      = intval($orderInfo['amount']);

		//请求参数准备
		$param['P_PostKey'] = $sign;

		return $this->gateway.'?'.http_build_query($param);
		//请求
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $this->gateway.'?'.http_build_query($param));
		$result = json_decode($response->getBody(),true);
var_dump($result);die;
		if(is_array($result)){
			if($result['rcode'] == '00'){
				return $result['b_purl'];
			}
			\Log::info('[RyPay] request error'.json_encode($result));
			return false;
		}
		\Log::info('[RyPay] request error 具体错误是啥，没记录，哈哈哈哈 '.$response->getBody());
		return false;

	}

	//加密
	public function genSign($params, $signKey){
        $str = $params['P_UserId'].'|'.$params['P_OrderId'].'|'.$params['P_CardId'].'|'.$params['P_CardPass']
            .'|'.$params['P_FaceValue'].'|'.$params['P_ChannelId'].'|'.$signKey;
        $post_key = md5($str);
        return $post_key;
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
	public function checkNotifySign($data){
        $tmp = $data['P_UserId']."|".$data["P_OrderId"]."|".$data["P_CardId"]."|".$data["P_CardPass"]."|". $data["P_FaceValue"]
            ."|".$data["P_ChannelId"]."|".$data["P_PayMoney"]."|".$data["P_ErrCode"]."|".$this->signKey;
        $getSign = md5($tmp);
		if( $data['P_PostKey'] == $getSign ){
			return true;
		} else {
			return false;
		}
	}
}