<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
	protected $table = 'user_logins';
    protected $fillable = ['user_id','ip','os'];
    public $timestamps = true;

    public static function log($userId, $ip, $os){
        $os  = ($os == 'android')  ? 2 : 1;
    	$obj = self::where('user_id', $userId)->where('created_at','>=', date('Y-m-d H:i:s',time() - 3600) );
    	if( $data = $obj->first() ){
    		return $data;
    	}
    	else{
    		return self::create([
    			'user_id'	=>	$userId,
    			'ip'	=>	$ip,
                'os'    =>  $os
    		]);
    	}
    }
}