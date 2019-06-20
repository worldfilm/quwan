<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authentication;

// use Illuminate\Notifications\Notifiable;

class UcUser extends Authentication {
    //use Notifiable;
    //选择数据库链接
    // protected $connection = '';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password', 'deposit', 'salt', 'phone', 'signage','signage_points','is_mute','status'
    ];

    /**
     * [findByUserName description]
     * @param  [type] $username [description]
     * @return [type]           [description]
     */
    public static function findByUserName( $username )
    {
        return self::where('username', $username)->where('status',8)->first();
    }

    /**
     * [findById description]
     * @param  [type] $ucId [description]
     * @return [type]       [description]
     */
    public static function findById( $ucId )
    {
        return self::where('id', $ucId)->where('status',8)->first();
    }

     /**
     * 通过手机获取用户信息
     * @param type $username
     * @return type
     */
    public static function findByPhone( $phone )
    {
        return self::where( 'phone', $phone )->where('status',8)->first();
    }

    /**
     * 通过识别码获取用户信息
     * @param type $username
     * @return type
     */
    public static function findBySignage( $signage )
    {
        return self::where( 'signage', $signage )->where('status',8)->first();
    }

    /**
     * 关联直播用户表
     * @return [type] [description]
     */
    public function user()
    {
        return $this->hasOne('App\Model\User', 'uc_id', 'id');
    }

    /**
     * 通过apiToken获取用户信息
     * @param $apiToken
     * @return Model|null|static
     */
    public static function findByApiToken($apiToken ){
        $user = self::where( 'api_token', $apiToken )->first();
        return $user;
    }

}
