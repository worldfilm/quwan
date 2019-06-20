<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cards extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'card_secret', 'status', 'use_id'
    ];


    /**
     * 获取配置列表
     * @return [type] [description]
     */
    public static function getList()
    {
        return  self::with('user')->orderBy('id','DESC')->paginate(10);
    }

    /**
     * 关联用户表
     * @return [type] [description]
     */
    public function user()
    {
        return $this->belongsTo('\App\Model\UcUser','use_id');
    }

}
