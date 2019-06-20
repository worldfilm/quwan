<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\Category;

class CategoryController extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	public function getList(Request $request){
		// $user = $request->user();
		// if( empty($user) || $user->vip_type != 3 ){
		// 	$response = [
		// 		'status'	=>	403,
		// 		'message'	=>	'需要白金会员'
		// 	];
		// 	return response()->json($response);
		// }
    	$category = $request->input('category');
    	// $page = intval($request->input('page',1));
    	// $pageSize = intval($request->input('page_size',20));
    	//return \App\Model\Category::getVipList(3);
    	$data = Category::getVipList();
    	$response = [
    		'status'	=>	0,
    		'message'	=>	'OK',
    		'data'	=>	$data,
    	];
    	return response()->json($response);
	}

}
