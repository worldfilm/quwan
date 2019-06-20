<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class VersionController extends BaseController
{
	public function getUpdate(Request $request){
		$vc = $request->input('vc');
		$os = strtolower($request->input('os'));
		$ch = $request->input('ch');
		if( $os == 'android' ){
			$info = \App\Model\Version::getAndroid($vc, $ch);
		}
		elseif( $os == 'ios' ){
			$info = \App\Model\Version::getIOS($vc, $ch);
		}
		else{
			$info = [];
		}
		if( empty($info) ){
			$update = false;
		}
		else{
			$update = true;
		}
		$response = [
			'status'	=>	0,
			'message'	=>	'OK',
			'data'	=>	[
				'update'	=>	$update,
				'info'	=>	$info ? $info : (object)[],
			],
		];
		return response()->json($response);
	}
}