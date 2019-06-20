<?php
return [
	'partner_id'	=>	'199550154446',
	'key'	=>	'2520f44f603cf8e79f160b30b3bd5877',
	'gateway'	=>	'https://pay.swiftpass.cn/pay/gateway',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/sppay',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/sppay',
	// 'notify_url'	=>	'http://127.0.0.1',
	// 'return_url'	=>	'http://127.0.0.1',
];