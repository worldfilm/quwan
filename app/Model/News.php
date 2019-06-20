<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class News extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'url', 'admin_id', 'status','rank'
    ];


    /**
     * 默认获取最新广播内容
     * @return [type] [description]
     */
    public static function getNews()
    {
        return self::where('status', 8)->orderBy('rank','asc')->orderBy('id','desc')->take(5)->get();
    }

}
