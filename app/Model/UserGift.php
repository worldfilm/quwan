<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserGift extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type','title','user_id', 'gift', 'before_gift', 'after_gift', 'status'
    ];



    /**
     * 关联用户表
     * @return [type] [description]
     */
    public function user()
    {
        return $this->belongsTo('\App\Model\User');
    }

}
