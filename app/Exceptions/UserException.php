<?php
namespace App\Exceptions;

use Exception;

class UserException extends Exception{

     protected $codes = [
        '50001' => '参数异常',
        '50002' => '日期范围不合法',
        '50003' => '没有天数的奖期规则必须指定起始期号',
        '50004' => '请正确输入起始奖期',
        '50005' => '数据库操作错误，请联系技术人员',
        '50006' => '彩种ID非法',
        '50007' => '必须指定时间', 
    ];

    public function __construct($code = 0, $message)
    {
        parent::__construct($message,$code);
    }
}