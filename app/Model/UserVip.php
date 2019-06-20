<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class UserVip extends Model
{
	protected $fillable = ['user_id','expire_at','out_trade_no','type','amount','is_first'];
	protected $table = 'users_vips';
	public static function createVip($userId, $itemInfo, $outTradeNo = '', $price, $type = 1){
		$existVip = self::getUserExistInfo($userId);

//		$unit = 'months';
		$unit = 'days'; //Berry说要改成天

		$period =  $itemInfo->period ? $itemInfo->period : 0;

		//如果不是充值购买vip  单位换成天
		if(!$outTradeNo){
			$unit = 'days';
		}

		if( $existVip ){
			$lastExpire = strtotime("+".$period." ".$unit,strtotime($existVip->expire_at));
		}
		else{
			$lastExpire = strtotime("+".$period." ".$unit);
		}

        $isFirstVip = self::query()->where('user_id',$userId)->first();

		$data = self::create([
			'user_id'	=>	$userId,
            'is_first'  => $isFirstVip ? 0 : 1,
			'amount'    =>  $price,
			'expire_at'	=>	date('Y-m-d H:i:s', $lastExpire),
			'out_trade_no'	=>	$outTradeNo,
			'type'      => $type,
		]);
		return $data;
	}

	public static function getUserExistInfo($userId){
		$data = self::where(['user_id'	=>	$userId])
					->where('expire_at', '>', date('Y-m-d H:i:s'))
					->orderBy('id','desc')
					->first();
		return $data;
	}

    public static function updateVip($userId, $addTime){

        try{
            $existVip = self::getUserExistInfo($userId);

            if( $existVip ){
                //存在则直接在这条记录上作修改,$addTime为分钟数,可正可负
                $newTime = strtotime($existVip->expire_at) + $addTime * 60;
                UserVip::query()->where('id',$existVip->id)->update(['expire_at' => date('Y-m-d H:i:s',$newTime)]);
            } else {
                $newTime = time() + $addTime * 60;
                self::query()->create([
                    'user_id'	=> $userId,
                    'is_first'  => 0,
                    'amount'    => 0,
                    'expire_at'	=> date('Y-m-d H:i:s', $newTime),
                    'out_trade_no'	=> "null",
                    'type'      => 2
                ]);
            }
        }catch(\Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }

    }

}