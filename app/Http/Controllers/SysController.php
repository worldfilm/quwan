<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Config;
use App\Model\Pay;

class SysController extends BaseController
{
	public function getNotice(Request $request){
		$vc = $request->input('vc');
		$os = strtolower($request->input('os'));
		$ch = $request->input('ch');
		$data = \App\Model\Notice::getByOS($ch, $os, $vc);
		if( empty($data) ){
			$response = [
				'status'	=>	-1,
				'message'	=>	'no notice'
			];
		}
		else{
			$response = [
				'status'	=>	0,
				'message'	=>	'OK',
				'data'	=>	$data,
			];
		}

		//====暂时屏蔽到公告，等后面版本再具体定方案====//
		$response = [
			'status'	=>	-1,
			'message'	=>	'no notice'
		];
		//====暂时屏蔽到公告，等后面版本再具体定方案====//

		return response()->json($response);
	}

	public function getPayList(Request $request){
		$os = $request->input('os');
		$payList = Pay::getPayMethod($os);
		$response = [
			'status'	=>	0,
			'message'	=>	'OK',
			'data'	=>	$payList,
		];
		return response()->json($response);
	}
}
