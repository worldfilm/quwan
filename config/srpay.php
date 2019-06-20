<?php
return [
	'id'	=>	'1487',
	'key'	=>	'30cb0bf4fabc40beb4024f4f2e896b9d',
	'gateway'	=>	'http://pay.shuorannet.com/chargebank.aspx',
	'gateway_domain'	=>	'http://pay.shuorannet.com',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/srpay',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/srpay',
];