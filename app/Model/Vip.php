<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Vip extends Model
{
	public static function getByTitle($name){
		return self::where('title',$name)->first();
	}

	public static function getList($needPay = true){
		if( $needPay ){
			$queryObj = self::where('price','>',0);
		}
		else{
			$queryObj = self::where('price','>=',0);
		}

		$data = $queryObj->select('id','title','price','period','dis_price')->get();

		foreach ($data as $key => $value) {
				$oldPrice = $value->price*$value->period;
				$newPrice = $value->dis_price*$value->period;
				$value->des = '节省'.ceil(($oldPrice-$newPrice)/$oldPrice*100).'% 总计￥'.$newPrice;
		}
		return $data;
	}

	public function category(){
		return $this->belongsToMany('\App\Model\Category','vips_categories','vip_id','category_id');
	}

}