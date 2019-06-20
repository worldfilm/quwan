<?php
return [
    'partner_id'            => "lianshengya",
    'merchantname'        => "成都联胜亚辉科技有限公司",
    'pfxpath'             => resource_path('ysepay') . "/lianshengya.pfx",
    'businessgatecerpath' => resource_path('ysepay') . "/businessgate.cer",
    'pfxpassword'         => "147258",
    'return_url'        => "http://pay.nmgflower.com/",
    'notify_url'        => "http://pay.nmgflower.com/pay/notify",
    'host'                => "pay.ysepay.com", //生产环境需更换为：pay.ysepay.com  60.201:889
    'xmlpage_uri'         => "/businessgate/yspay.do",
    'xmlbackmsg_uri'      => "/businessgate/xmlbackmsg.do",
    'filemsg_uri'         => "/businessgate/filemsg.do",
];