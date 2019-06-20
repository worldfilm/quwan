<?php
if( env('APP_ENV') == 'local' ){
	$payDomain = 'pay.nmgflower.com';
}
else{
	$payDomain = 'pay.jzxyxxw.com';
}
return [
	'pay'	=>	$payDomain,
];
