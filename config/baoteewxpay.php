<?php
return [
	'area'	=>	'wuxian003',
	//'key'	=>	'7g6jsj332llfhwwi45slg298908o1lyb',
	'key'	=>	'82536dccb434769c560ecefa17edb440',
	'gateway'	=>	'http://apay.sdcxbj.com/jcpapay/Pay',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/baoteewx',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/baoteewx',
];
