<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at','updated_at', 'channel', 'os'];

    /**
     * 通过apiToken获取用户信息
     * @param type $apiToken
     * @return type
     */
    public static function findByApiToken( $apiToken )
    {
        $user = self::where( 'api_token', $apiToken )->first();
        return $user;
    }

    /**
     * 通过用户名获取用户信息
     * @param type $username
     * @return type
     */
    public static function findByNickName( $nickname )
    {
        return self::where( 'nickname', $nickname )->first();
    }


    /**
     * 通过用户主表获取用户信息
     * @param  [int] $ucId [主表用户ID]
     * @return [object]
     */
    public static function findByUcId( $ucId )
    {
        return self::with('ucUser')->where( 'uc_id', $ucId )->first();
    }

    public static function findByUid( $uid )
    {
        return self::query()->where( 'id', $uid )->first();
    }

    public static function findByPhone( $phone )
    {
        return self::query()->where( 'phone', $phone )->where('status',8)->first();
    }

    /**
     * 通过Id用户信息
     * @param  [int] $id
     * @return [object]
     */
    public static function findById( $id )
    {
        return self::where( 'id', $id )->first();
    }

    /**
     * 判断是否是VIP
     * @param  [type]  $user [description]
     * @return boolean       [description]
     */
    public static function isVip( $user )
    {
        $isVip = $user->vip()->where('expire_at', '>', date('Y-m-d H:i:s'))->orderBy('id', 'desc')->first();
        return $isVip ? $isVip : false;
    }

    /**
     * 获取用户有效期Vip等级
     */
    public static function getUserVip( $user ){
        $is_vip = self::isVip( $user );
        return $is_vip ? $user->vip_type : false;
    }

    /**
     * 获取用户收藏
     * @param  [type]  $user     [description]
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
    public static function getCollectList($user, $page = 1, $pageSize =20)
    {
        $total = $user->collect()->count();
        $ret = [];

        $collects = $user
                    ->collect()
                    ->with('video')
                    ->skip(($page - 1) * $pageSize)
                    ->take($pageSize)
                    ->get();

        //收藏为空
        $list = $pages = [];
        if( count($collects) > 0 ){
            foreach( $collects as $value ) {
                if(!$value->video){
                    continue;
                }
                $list[] = [
                    'id'            => hashid($value->video->id, 'video'),
                    'title'         => $value->video->title,
                    'thumb_img_url' => $value->video->thumb_img_url,
                    'like'          => $value->video->likes ? ceil(($value->video->likes / ($value->video->likes + $value->video->dislikes)) * 100) : 0,
                    'views'         => $value->video->view_times,
                ];
            }

            $pages = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];

            $ret['page'] = $pages;
        }

        $ret['list'] = $list;

        return $ret;
    }

    /**
     * 关联VIP表
     * @return type
     */
    public function vip()
    {
        return $this->hasMany("App\Model\UserVip", 'user_id');
        // $vipInfo = \App\Model\UserVip::getUserExistInfo($this->id);
        // if( !empty($vipInfo) ){
        //     $vipInfo->expire_at = strtotime($vipInfo->expire_at);
        // }
        // return $vipInfo;
    }

    /**
     * 关联设备表
     * @return [type] [description]
     */
    public function identifiers()
    {
        return $this->hasMany('App\Model\UserIdentifiers');
    }

    /**
     * 关联收藏表
     * @return [type] [description]
     */
    public function collect()
    {
        return $this->hasMany('App\Model\UserCollects');
    }

    /**
     * 关联主账号表
     * @return [type] [description]
     */
    public function ucUser()
    {
        return $this->hasOne('App\Model\UcUser','id','uc_id');
    }

}
