<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Recharge extends Model {

    protected $table = 'recharge';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'dis_price', 'level_limit','description'
    ];

    /**
     * [getRechargeList 获取充值列表]
     * @return [type] [description]
     */
    public static function getRechargeList()
    {
        $data = self::orderBy('price')->get();
        $i = 0;
        foreach ($data as $key => $value) {
            $value->i = $i;
            $i++;
            $newPrice = ($value->price-$value->dis_price);
            $value->des = '';
            if($newPrice){
                $value->des = '首购优惠<span>'.ceil($newPrice/$value->price*100).'%</span> 节省<span>'.$newPrice.'</span>元';
            }
        }

        return $data;
    }
}
