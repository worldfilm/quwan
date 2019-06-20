<?php
return [
	'partner_id'	=>	'',
	'key'	=>	'fde5af9951ea122008a9ecdd9ee218ca',
	'check_key'	=>	'fde5af9951ea122008a9ecdd9ee218ca',
	'gateway'	=>	'http://47.52.31.88:3000',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/trans',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/trans',
];