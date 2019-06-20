<?php
	/**
	 * 3.1
	 * @param  [string] $buId      [Agent Id]
	 * @param  [string] $priKey    [Agent key]
	 * @param  [array] $userParms  array("Loginname"=>, "Secure Token"=>, "NickName"=>, "Oddtype"=>"A", "Cur"=>"CNY")
	 * @param  [string] $APIUrl    [API Url]
	 * @return [boolean]           [result]
	 */
	function api_player_create($buId, $priKey, $userParms, $APIUrl)
	{
		$createRet = send_api_get_obj($APIUrl, get_format_string($buId, "lg", $userParms), $priKey);
		return json_encode($createRet);
	}

	/**
	 * 3.1
	 * @param  [string] $buId      	[Agent Id]
	 * @param  [string] $priKey    	[Agent key]
	 * @param  [array] $userParms 	array("Loginname"=>, "SecureToken"=>)
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [boolean]        	[result]
	 */
	function api_player_login($buId, $priKey, $userParms, $APIUrl)
	{
		$lgRet = send_api_get_obj($APIUrl, get_format_string($buId, "lg", $userParms), $priKey);
		return json_encode($lgRet);
	}
	
	/**
	 * 3.2
	 * @param  [string] $buId     	[Agent Id]
	 * @param  [string] $priKey   	[Agent key]
	 * @param  [array]  $userParms 	array("Loginname"=>, "Cur"=>"CNY",)
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [string]         	[balance]
	 */
	function api_player_balance($buId, $priKey, $userParms, $APIUrl)
	{
		$gbRet = send_api_get_obj($APIUrl, get_format_string($buId, "gb", $userParms), $priKey);
		return json_encode($gbRet);
	}
	
	/**
	 * 3.3
	 * @param  [string] $buId      [Agent Id]
	 * @param  [string] $priKey    [Agent Key]
	 * @param  [array]  $userParms array("Loginname"=>, "SecureToken"=>,"GameId"=>, "Cur"=>"CNY","Oddtype"=>"A","HomeURL"=>)
	 * @param  [string] $APIUrl    [API Url]
	 * @return [string]            [Game link]
	 */
	function api_fw_game_opt($buId, $priKey, $userParms, $APIUrl)
	{
		$fwRet = send_api_get_obj($APIUrl, get_format_string($buId, "fwgame_opt", $userParms), $priKey);
		return $fwRet;
	}

	/**
	 * 3.4-3.5
	 * @param  [string] $buId      	[Agent Id]
	 * @param  [string] $priKey    	[Agent key]
	 * @param  [array]  $userParms 	array("Loginname"=>, "Billno"=>(First letter should Big), "Credit"=>(integer and less than one million ), "Cur"=>"CNY")
	 * @param  [string] $APIUrl  	[API Url]
	 * @return [boolean]        	[result]
	 */
	function api_player_trans_deposit($buId, $priKey, $userParms, $APIUrl)
	{

		$userParms['Type'] = "100";
		$tcRet = send_api_get_obj($APIUrl, get_format_string($buId, "tc", $userParms), $priKey);

		if($tcRet->Status == "1")
		{
			$remoteOrderNumber = $tcRet->Data;
			$userParms['TGSno'] = $remoteOrderNumber;
			$tccRet = send_api_get_obj($APIUrl, get_format_string($buId, "tcc", $userParms), $priKey);
			return json_encode($tccRet);
		}
		else
		{
			return json_encode($tcRet);
		}
	}

	/**
	 * 3.4-3.5
	 * @param  [string] $buId     	[Agent Id]
	 * @param  [string] $priKey   	[Agent key]
	 * @param  [array] $userParms 	array("Loginname"=>, "Billno"=>(First letter should Big), "Credit"=>(integer and less than one million ), "Cur"=>"CNY")
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [boolean]       	 	[result]
	 */
	function api_player_trans_withdrawal($buId, $priKey, $userParms, $APIUrl)
	{
		$userParms['Type'] = "200";
		$tcRet = send_api_get_obj($APIUrl, get_format_string($buId, "tc", $userParms), $priKey);

		if($tcRet->Status == "1")
		{
			$userParms['TGSno'] = $tcRet->Data;
			$tccRet = send_api_get_obj($APIUrl, get_format_string($buId, "tcc", $userParms), $priKey);
			return json_encode($tccRet);
		}else
		{
			return json_encode($tcRet);
		}
	}
	
	/**
	 * 3.7
	 * @param  [string] $buId     	[Agent Id]
	 * @param  [string] $priKey   	[Agent key]
	 * @param  [array]  $userParms 	array()
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [string]         	[JSON]
	 */
	function api_player_getJPNumber($buId, $priKey, $userParms, $APIUrl)
	{
		$gbRet = send_api_get_obj($APIUrl, get_format_string($buId, "gjp", $userParms), $priKey);
		return json_encode($gbRet);
	}

	 /**
	 * 3.8
	 * @param  [string] $buId     	[vendor Id]
	 * @param  [string] $priKey   	[vendor key]
	 * @param  [array]  $userParms 	array("Start"=>, "End"=>)
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [string]         	[JSON]
	 */
	function api_player_getPagesWithDate($buId, $priKey, $userParms, $APIUrl)
	{
		$gbRet = send_api_get_obj($APIUrl, get_format_string($buId, "GET_PAGES_DETAIL_WITH_DATE", $userParms), $priKey);
		return json_encode($gbRet);
	}
	
	/**
	 * 3.9
	 * @param  [string] $buId     	[vendor Id]
	 * @param  [string] $priKey   	[vendor key]
	 * @param  [array]  $userParms 	array("Start"=>, "End"=>,"PageNum"=>)
	 * @param  [string] $APIUrl   	[API Url]
	 * @return [string]         	[JSON]
	 */
	function api_player_getRecordsWithDateOnPage($buId, $priKey, $userParms, $APIUrl)
	{
		$gbRet = send_api_get_obj($APIUrl, get_format_string($buId, "GET_RECORDS_WITH_DATE_ON_PAGE", $userParms), $priKey);
		return json_encode($gbRet);
	}
	
	/**
	 * [Send GET Request]
	 * @param  [string] $url    [API URL]
	 * @param  [string] $fParms [params]
	 * @param  [string] $priKey [Vendor key]
	 * @return [obj]          [obj->Status = 0 success, 1 fail, obj->Data= , obj->ErrorCode=, obj->ErrorMsg= ]
	 */
	function send_api_get_obj($url, $fParms, $priKey)
	{
		$postArray =[];
		$postArray['params'] = get_aes_string($fParms);
		$postArray['key'] = md5($fParms.$priKey);
		$url = $url."?params=".$postArray['params']."&key=".$postArray['key'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);
		$result = [];

		$return_obj = new stdclass;
		$return_obj->Status = "0";
		$return_obj->Data = "";
		$return_obj->ErrorCode = "";
		$return_obj->ErrorMsg = "";

		if (!curl_errno($ch)) {
			switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
				case 200:  # OK
					$return_obj = json_decode($output);
					break;
				default:
					$return_obj->ErrorCode = $http_code;
					$return_obj->ErrorMsg = "Unexpected HTTP code: ".$http_code;
			}
		}
		curl_close($ch);
		return $return_obj;
	}

	/**
	 * [Get AES Encrypt params String]
	 * @param  [string] $params [Unencrypt params]
	 * @return [string]        [Encrypt params]
	 */
	function get_aes_string($params)
	{
		$AESKey = "3f203ac36b4262ba28afa0366f4a63b9";
		$AESIV = "650DD7EB2B195F0A";
		return openssl_encrypt($params, 'aes-256-cbc', $AESKey, 0, $AESIV);
	}


	/**
	 * [Get Valid API params string]
	 * @param  [string] $buId     [Vendor Id]
	 * @param  [string] $method   [API method]
	 * @param  [array] $dtlParms  [API params]
	 * @return [string]           [Valid get string]
	 */
	function get_format_string($buId, $method, $dtlParms)
	{
		$postString = "CTGent=".$buId.",Method=".$method;
		$parmsString = "";
		foreach ($dtlParms as $key => $value) {
		  	$parmsString = $parmsString.$key."=".$value.",";
		}
		return $postString.",".$parmsString;
	}
 ?>
