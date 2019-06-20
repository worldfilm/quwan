<?php
return [
	'merId'	=>	'M15000012',
	'key'	=>	'Z893FNYR6VVDQSW53PDM0J0H7CEAWJVB',
	'gateway'	=>	'https://pay.wechata.com',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/microwe',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/microwe',
];