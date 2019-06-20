<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserCollects extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'video_id'
    ];


    /**
     *  通过条件删除关联
     */
    public static function del($userId,$videoId)
    {
        return self::where('user_id',$userId)->where('video_id',$videoId)->delete();
    }

    /**
     * 获取关联video
     * @param  [type] $userId [description]
     * @return [type]         [description]
     */
    public static function getCollect($userId,$videoId)
    {
        return self::where('user_id',$userId)->where('video_id',$videoId)->get();
    }

    /**
     * 关联用户表
     * @return [type] [description]
     */
    public function user()
    {
        return $this->belongsTo('\App\Model\User');
    }

    /**
     * 关联视频表
     * @return [type] [description]
     */
    public function video()
    {
        return $this->belongsTo('\App\Model\Video', 'video_id', 'id');
    }

}
