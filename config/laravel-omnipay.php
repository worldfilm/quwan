<?php

return [

	// The default gateway to use
	'default' => 'alipay',

	// Add in each gateway here
	'gateways' => [
		'paypal' => [
			'driver'  => 'PayPal_Express',
			'options' => [
				'solutionType'   => '',
				'landingPage'    => '',
				'headerImageUrl' => ''
			]
		],
		'alipay' => [
            'driver' => 'Alipay_Express',
            'options' => [
                'partner' => '2088922836331915',
                'key' => '2018012302037119',
                'sellerEmail' =>'3276553853@qq.com',
                'returnUrl' => 'your returnUrl here',
                'notifyUrl' => 'http://www.dajin0571.com/api/alipay/notify'
            ]
        ]
	]

];