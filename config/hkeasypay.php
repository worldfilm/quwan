<?php
return [
	'host'	=>	'http://pay.hkeasypay.org',
	'company_code'	=>	'8833',
	'md5_key'	=>	'BoRktzMfWymihVkJ',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/hkeasypay',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/pay/return/hkeasypay',
];