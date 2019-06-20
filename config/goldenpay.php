<?php
return [
	'host'	=>	'https://www.goldenpay88.com',
	'appid'	=>	'201705151514025',
	'clientid'	=>	'201705151403177',
	'apikey'	=>	'f5821de80bfc3bf3170732d1cb55cf1c',
	'server_pub_key'	=>	'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCabJDz/66tGW6J0SBHI3zTqz+vB7lkBwEcSnnaNJ6mAZ64Garc4Ax9lcFV9aUI3/v/w7LRnhPRnMCHc9HeBFS66jPixlvk3cB/TYsVoxuQInTE/VmQDv+9cRlKYpemULGr6VoeOzAoEHz68g/YUZCjFBxbhTyOKutBoCorsAmQeQIDAQAB',
	'notify_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/notify/goldenpay',
	'return_url'	=>	'http://'. env('APP_DOMAIN') .'/api/pay/return/goldenpay',
	'private_key_path'	=>	resource_path('goldenpay') . '/private_key.pem',
	'server_public_key_path'	=>	resource_path('goldenpay') . '/server_public_key.pem',
];