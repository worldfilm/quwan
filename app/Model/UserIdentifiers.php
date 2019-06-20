<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserIdentifiers extends Model {

    //use Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'identifier'
    ];
    public $timestamps = false;

    /**
     * 通过设备号查找
     * @param  [type] $identifier [description]
     * @return [type]             [description]
     */
    public static function findByIdentifier( $identifier )
    {   return self::leftJoin('users','users.id','=','user_identifiers.user_id')
              ->where('user_identifiers.identifier', $identifier)
              ->where('user_identifiers.user_id','!=','')
              ->orderBy('users.updated_at','DESC')
              ->first();
        // return self::with(['user'=>function($query){
        //     $query->where('uc_id','!=','')->orderBy('updated_at','DESC');
        // }])->where('identifier', $identifier)->where('user_id','!=','')->first();
    }

    /**
     * 更新关联
     * @param  [type] $uid        [description]
     * @param  [type] $identifier [description]
     * @return [type]             [description]
     */
    public static function deleteOrCreate( $uid, $identifier )
    {
        $result = self::where('identifier', $identifier)->pluck('id')->all();

        self::whereIn('id', $result)->delete();
        self::create(['user_id' => $uid, 'identifier' => $identifier]);

    }

    /**
     * 反向关联User表
     * @return object
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User');
    }

}
