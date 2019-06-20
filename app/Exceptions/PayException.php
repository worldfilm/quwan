<?php
namespace App\Exceptions;

use Exception;

class PayException extends Exception{

     protected $codes = [
        '2001' => '卡密不存在或已使用',
        '2002' => '购买失败',
        '2003' => '星票余额不足',
        '2004' => '星钻余额不足，请先充值',
        '2005' => '换购类型错误',
        '2006' => '查询账单类型有误',
        '2007' => '支付通道有误，请刷新页面',
        '2008' => '购买失败，座驾类型有误',
        '2009' => '操作失败',
    ];

    public function __construct($code = 0, $message='')
    {
    	$code = isset($this->codes[$code]) ? $code : 2002;
        parent::__construct($this->codes[$code],$code);
    }
}