<?php
return [
	'area'	=>	'niniwx001',
	'key'	=>	'b40c1372339011b881ffdec1f9764a92',
	'gateway'	=>	'http://lxhtqa.cn:1080',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/baotee',
	// 'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/baotee',
	'return_url'	=>	'http://'. config('domain.pay') .'/api/pay/return/baotee',
];