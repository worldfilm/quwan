<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Version extends Model
{
	CONST TYPE_ANDROID = 1,
		  TYPE_IOS = 2;
	public static function getAndroid($vc, $ch){
		$currentVersion = self::where('type',self::TYPE_ANDROID)->where('status',1)->orderBy('id','desc')->first();
		if( !empty($currentVersion)){
			if( version_compare($currentVersion->version_code, $vc,'>') ){
				$downloadInfo = json_decode($currentVersion->download_url,true);
				if( isset($downloadInfo[$ch]) ){
					$downloadUrl = $downloadInfo[$ch];
					$ret = [
						'version_code'	=>	$currentVersion->version_code,
						'version_name'	=>	$currentVersion->version_name,
						'download_url'	=>	$downloadUrl,
						'description'	=>	$currentVersion->description,
					];
					return $ret;
				}
				else{
					return [];
				}
			}
		}
		return [];
	}

	public static function getIOS($vc, $ch){
		$currentVersion = self::where('type',self::TYPE_IOS)->where('status',1)->orderBy('id','desc')->first();
		if( !empty($currentVersion)){
			if( version_compare($currentVersion->version_code, $vc,'>') ){
				$downloadInfo = json_decode($currentVersion->download_url,true);
				if( isset($downloadInfo[$ch]) ){
					$downloadUrl = $downloadInfo[$ch];
					$ret = [
						'version_code'	=>	$currentVersion->version_code,
						'version_name'	=>	$currentVersion->version_name,
						'download_url'	=>	$downloadUrl,
						'description'	=>	$currentVersion->description,
					];
					return $ret;
				}
				else{
					return [];
				}
			}
		}
		return [];
	}
}