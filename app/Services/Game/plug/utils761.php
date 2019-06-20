<?php
    function ParamEncode($param, $config)
    {
        $iv = substr($config['APPKEY'],0,16);
        $method="AES-256-CBC";
    
        $data = json_encode($param);
        $s1 = openssl_encrypt($data, $method, $config['APPKEY'] , true, $iv);
        return base64_encode($s1);
    }

    function ParamDecode($param_str, $config)
    {
        $iv = substr($config['APPKEY'],0,16);
        $method = "AES-256-CBC";

        $data = base64_decode($param_str);
        $s1 = openssl_decrypt($data, $method ,$config['APPKEY'] , true, $iv);
        return json_decode($s1);
    }

    function MakeSign($timestamp, $config)
    {
        $sign_str = "".$config['AGENT'].$timestamp.$config['APPKEY'];
        return strtoupper(md5($sign_str));
    }

    function combineURL($baseURL,$agv)
    {
        $combined = $baseURL."?";
        $valueArr = array();
        foreach($agv as $key => $val){
            $valueArr[] = "$key=$val";
        }
        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);
        return $combined;
    }
   
    function CallAPI($method,$params_raw,$config)
    {
        $params = ParamEncode($params_raw, $config);
        $timestamp = time();
        $sign = MakeSign($timestamp, $config);

        $rqagv = array (
            "agent" => $config['AGENT'],
            "timestamp" => $timestamp,
            "params" => URLEncode($params),
            "sign" => $sign
        );

        $url = combineURL($config['APIURL'].$method,$rqagv);
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return json_decode($file_contents, true);
    }
?>