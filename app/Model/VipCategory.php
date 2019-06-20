<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class VipCategory extends Model
{
	protected $table = 'vips_categories';
	CONST TYPE_FREE_VIP_ID = 0,
		  TYPE_VIP_VIP_ID =1;

	public static function getCategoryList($type = 'free'){
		if( $type == 'free' ){
			$vipId = self::TYPE_FREE_VIP_ID;
		}
		elseif( $type == 'vip' ){
			$vipId = self::TYPE_VIP_VIP_ID;
		}
		$data = self::orderBy('id','desc')->where('vip_id', $vipId)->get();
		$list = [];
		foreach ($data as $key => $value) {
			$list[] = $value->category->id;
		}
		return $list;
	}

	public function category(){
		return $this->hasOne('\App\Model\Category','id','category_id');
	}
}
