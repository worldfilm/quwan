<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class PayConfig extends Model{

	private $config = [
		'alipay'	=>	[
			'name'	=>	'支付宝',
			'os'	=>	['ios','android'],
			'list'	=>	[
				[
					'channel'	=>	'Baoteepay',
					'method'	=>	'wap',
				],
			],
		 ],
 	 // 	'wechat'	=>	[
		 // 	'name'	=>	'微信',
		 // 	'os'	=>	['ios','android'],
		 // 	'list'	=>	[
		 // 		[
		 // 			'channel'	=>	'Lepay',
		 // 			'method'	=>	'wap',
		 // 		],
		 // 	]
		 // ],
		 	'wechat'	=>	[
		 	'name'	=>	'微信',
		 	'os'	=>	['ios','android'],
		 	'list'	=>	[
		 		[
		 			'channel'	=>	'Baoteewxpay',
		 			'method'	=>	'wap',
		 		],
		 	]
		 ],

	];
	public static function getConfig(){
		return [
			'method'	=>	'qr',
			'channel'	=>	'Microwepay',
		];
	}

	public function getList($os = ''){
		$payList = [];
		$os = strtolower($os);
		foreach ($this->config as $key => $value) {
			if( !empty($os) ){
				if( !in_array(strtolower($os), $value['os']) ){
					continue;
				}
			}
			$payList[] = [
				'method'	=>	$key,
				'name'	=>	$value['name'],
			];
		}
		return $payList;
	}

	public function getChannel($payMethod = '', $os = ''){
		// $config =
		$payConfigPool = isset( $this->config[$payMethod] ) ? $this->config[$payMethod] : false;
		$payConfigList = $payConfigPool['list'];
		$payConfig = $payConfigList[array_rand($payConfigList)];
		if( empty($payConfig) ){
			return false;
		}
		else{
 			if( !empty($os) ){
				if( in_array( $os,(array)$payConfigPool['os']) ){
					return $payConfig;
				}
				else{
					return false;
				}
			}
			else{
				return $payConfig;
			}
			return $payConfig;
		}
	}
}
